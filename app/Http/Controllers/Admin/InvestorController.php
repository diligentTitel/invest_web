<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvestorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(['active', 'suspended'])],
        ]);

        $investors = $this->investors()
            ->with('investorProfile')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('investorProfile', fn (Builder $profile) => $profile->where('legal_name', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.investors.index', compact('investors', 'filters'));
    }

    public function show(User $user, Request $request): View
    {
        $investor = $this->investors()
            ->with(['role:id,name,slug', 'investorProfile'])
            ->findOrFail($user->id);
        $canReviewKyc = $request->user()->hasPermission('kyc.review');
        $kycSubmissions = $canReviewKyc
            ? KycSubmission::query()
                ->where('user_id', $investor->id)
                ->latest('created_at')
                ->get(['id', 'provider', 'status', 'submitted_at', 'reviewed_at'])
            : collect();
        $canViewAuditLog = $request->user()->hasPermission('reports.view');
        $recentAuditEvents = $canViewAuditLog
            ? AuditLog::query()->with('actor:id,name')
                ->where('subject_type', $investor->getMorphClass())
                ->where('subject_id', $investor->id)
                ->latest('created_at')->limit(8)->get()
            : collect();

        return view('admin.investors.show', compact(
            'investor',
            'kycSubmissions',
            'canReviewKyc',
            'canViewAuditLog',
            'recentAuditEvents',
        ));
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['active', 'suspended'])],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $newStatus = $validated['status'];

        $changed = DB::transaction(function () use ($request, $user, $validated, $newStatus): bool {
            $investor = $this->investors()->lockForUpdate()->findOrFail($user->id);
            $previousStatus = (string) $investor->status;

            if ($previousStatus === $newStatus) {
                return false;
            }

            $investor->forceFill(['status' => $newStatus])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()->id,
                'action' => $newStatus === 'suspended' ? 'investor.suspended' : 'investor.reactivated',
                'subject_type' => $investor->getMorphClass(),
                'subject_id' => $investor->getKey(),
                'request_id' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 2000, ''),
                'properties' => [
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'reason' => trim($validated['reason']),
                ],
                'created_at' => now(),
            ]);

            return true;
        });

        $message = $changed
            ? ($newStatus === 'suspended' ? 'Investor access has been suspended.' : 'Investor access has been restored.')
            : 'The investor account already has that access status.';

        return redirect()->route('admin.investors.show', $user)->with('status', $message);
    }

    private function investors(): Builder
    {
        return User::query()
            ->where('is_demo', false)
            ->whereHas('role', fn (Builder $query) => $query->where('slug', 'investor'));
    }
}
