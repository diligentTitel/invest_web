<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'posted', 'failed', 'reversed'])],
        ]);

        $transactions = FinancialTransaction::query()
            ->with(['actor:id,name,email', 'entries.account:id,account_code,account_type,currency'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('transaction_type', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc(DB::raw('COALESCE(posted_at, created_at)'))
            ->paginate(20)
            ->withQueryString();

        $transactions->getCollection()->each(fn (FinancialTransaction $transaction) => $this->formatAmounts($transaction));

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'filters' => $filters,
        ]);
    }

    public function show(FinancialTransaction $transaction): View
    {
        $transaction->load([
            'actor:id,name,email',
            'entries.account:id,account_code,account_type,currency',
        ]);
        $this->formatAmounts($transaction);

        return view('admin.transactions.show', compact('transaction'));
    }

    private function formatAmounts(FinancialTransaction $transaction): void
    {
        $transaction->entries->each(function ($entry): void {
            $precision = (int) config('platform.currency_precision.'.$entry->currency, 2);
            $entry->setAttribute('display_amount', (string) BigDecimal::of((string) $entry->amount)->toScale($precision));
        });
    }
}