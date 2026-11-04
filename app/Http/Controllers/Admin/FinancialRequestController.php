<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\WithdrawalRequest;
use Brick\Math\BigDecimal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialRequestController extends Controller
{
    public function deposits(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:32'],
        ]);

        $items = DepositRequest::query()
            ->whereHas('user', fn (Builder $query) => $query->where('is_demo', false))
            ->with(['user:id,name,email', 'cryptocurrency:id,code,name', 'network:id,code,name'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('reference', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();
        $this->formatPageAmounts($items, 'requested_amount', 'currency_code');

        return view('admin.requests.index', [
            'items' => $items,
            'filters' => $filters,
            'queueType' => 'deposit',
            'canViewInvestors' => $request->user()->hasPermission('investors.view'),
        ]);
    }

    public function deposit(DepositRequest $depositRequest, Request $request): View
    {
        $record = DepositRequest::query()
            ->whereHas('user', fn (Builder $query) => $query->where('is_demo', false))
            ->with([
                'user:id,name,email',
                'cryptocurrency:id,code,name',
                'network:id,code,name',
                'provider:id,display_name,provider_key',
                'transactions' => fn ($query) => $query->latest('created_at'),
                'transactions.network:id,code,name',
            ])
            ->findOrFail($depositRequest->id);

        $record->setAttribute('display_amount', $this->formatAmount($record->requested_amount, $record->currency_code));

        return view('admin.requests.show', [
            'record' => $record,
            'queueType' => 'deposit',
            'canViewInvestors' => $request->user()->hasPermission('investors.view'),
        ]);
    }

    public function withdrawals(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:32'],
        ]);

        $items = WithdrawalRequest::query()
            ->whereHas('user', fn (Builder $query) => $query->where('is_demo', false))
            ->with(['user:id,name,email', 'network:id,code,name', 'account:id,account_code,currency'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('reference', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();
        $this->formatPageAmounts($items, 'amount', 'currency');

        return view('admin.requests.index', [
            'items' => $items,
            'filters' => $filters,
            'queueType' => 'withdrawal',
            'canViewInvestors' => $request->user()->hasPermission('investors.view'),
        ]);
    }

    public function withdrawal(WithdrawalRequest $withdrawalRequest, Request $request): View
    {
        $record = WithdrawalRequest::query()
            ->whereHas('user', fn (Builder $query) => $query->where('is_demo', false))
            ->with(['user:id,name,email', 'network:id,code,name', 'account:id,account_code,currency', 'reviewer:id,name'])
            ->findOrFail($withdrawalRequest->id);

        $record->setAttribute('display_amount', $this->formatAmount($record->amount, $record->currency));

        return view('admin.requests.show', [
            'record' => $record,
            'queueType' => 'withdrawal',
            'canViewInvestors' => $request->user()->hasPermission('investors.view'),
        ]);
    }

    private function formatPageAmounts(LengthAwarePaginator $items, string $amountColumn, string $currencyColumn): void
    {
        $items->getCollection()->each(function ($item) use ($amountColumn, $currencyColumn): void {
            $item->setAttribute('display_amount', $this->formatAmount(
                $item->getAttribute($amountColumn),
                (string) $item->getAttribute($currencyColumn),
            ));
        });
    }

    private function formatAmount(?string $amount, string $currency): string
    {
        if ($amount === null) {
            return 'Not recorded';
        }

        $precision = (int) config('platform.currency_precision.'.$currency, 2);

        return (string) BigDecimal::of($amount)->toScale($precision);
    }
}
