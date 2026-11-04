<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use App\Models\LedgerEntry;
use Brick\Math\BigDecimal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $accounts = FinancialAccount::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['entries' => fn ($query) => $query->whereHas(
                'transaction',
                fn ($transactions) => $transactions->where('status', 'posted')
            )])
            ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balance = BigDecimal::zero();
            $normalCredit = in_array($account->account_type, ['liability', 'equity', 'income'], true);

            foreach ($account->entries as $entry) {
                $amount = BigDecimal::of((string) $entry->amount);
                $increases = $normalCredit ? $entry->direction === 'credit' : $entry->direction === 'debit';
                $balance = $increases ? $balance->plus($amount) : $balance->minus($amount);
            }

            $balances[$account->currency] = ($balances[$account->currency] ?? BigDecimal::zero())->plus($balance);
        }

        $activity = LedgerEntry::query()
            ->whereHas('account', fn ($query) => $query->where('user_id', $user->id)->where('status', 'active'))
            ->whereHas('transaction', fn ($query) => $query->where('status', 'posted'))
            ->with([
                'account:id,account_type,currency',
                'transaction:id,reference,transaction_type,description,posted_at',
            ])
            ->latest('created_at')
            ->limit(10)
            ->get();

        $activity->each(function (LedgerEntry $entry): void {
            $precision = (int) config('platform.currency_precision.'.$entry->currency, 2);
            $entry->setAttribute('display_amount', (string) BigDecimal::of((string) $entry->amount)->toScale($precision));
        });

        $recordCount = LedgerEntry::query()
            ->whereHas('account', fn ($query) => $query->where('user_id', $user->id)->where('status', 'active'))
            ->whereHas('transaction', fn ($query) => $query->where('status', 'posted'))
            ->count();

        $trendStart = now()->startOfMonth()->subMonths(5);
        $trendEntries = LedgerEntry::query()
            ->whereHas('account', fn ($query) => $query->where('user_id', $user->id)->where('status', 'active'))
            ->whereHas('transaction', fn ($query) => $query->where('status', 'posted')->where('posted_at', '>=', $trendStart))
            ->with('transaction:id,posted_at')
            ->get();

        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $activityTrend = [
            'labels' => $months->map(fn ($month) => $month->format('M Y'))->all(),
            'values' => $months->map(fn ($month) => $trendEntries->filter(
                fn (LedgerEntry $entry) => $entry->transaction?->posted_at?->format('Y-m') === $month->format('Y-m')
            )->count())->all(),
        ];

        return view('investor.dashboard', [
            'balances' => collect($balances)->map(fn (BigDecimal $amount, string $currency) => (string) $amount->toScale((int) config('platform.currency_precision.'.$currency, 2))),
            'recordCount' => $recordCount,
            'activity' => $activity,
            'activityTrend' => $activityTrend,
        ]);
    }
}