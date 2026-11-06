<?php

namespace App\Http\Controllers\Investor;

use App\Domain\Investments\MarketDataService;
use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use App\Models\InvestmentAsset;
use App\Models\LedgerEntry;
use App\Models\SimulatedPosition;
use Brick\Math\BigDecimal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(MarketDataService $marketData): View
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

        $positions = $user->simulatedPositions()->with('asset')->get()
            ->filter(fn (SimulatedPosition $position) => BigDecimal::of($position->quantity)->isGreaterThan(BigDecimal::zero()))
            ->values();
        $portfolioValue = BigDecimal::zero();
        $investedAmount = BigDecimal::zero();
        $unrealizedPnl = BigDecimal::zero();
        $valuedCount = 0;
        $unvaluedCount = 0;
        $positionRows = collect();
        $categoryValues = [];

        foreach ($positions as $position) {
            $quantity = BigDecimal::of($position->quantity);
            $costBasis = BigDecimal::of($position->cost_basis);
            $investedAmount = $investedAmount->plus($costBasis);
            $quote = $marketData->quote($position->asset);
            if (! $quote && $position->manual_price) {
                $quote = [
                    'price' => (float) $position->manual_price,
                    'updated_at' => $position->manual_price_updated_at,
                    'source' => 'User-entered estimate',
                ];
            }

            $value = null;
            $gain = null;
            if ($quote && is_numeric($quote['price'] ?? null)) {
                $value = BigDecimal::of((string) $quote['price'])->multipliedBy($quantity);
                $gain = $value->minus($costBasis);
                $portfolioValue = $portfolioValue->plus($value);
                $unrealizedPnl = $unrealizedPnl->plus($gain);
                $categoryValues[$position->asset->category] = ($categoryValues[$position->asset->category] ?? BigDecimal::zero())->plus($value);
                $valuedCount++;
            } else {
                $unvaluedCount++;
            }

            $positionRows->push([
                'position' => $position,
                'quote' => $quote,
                'value' => $value,
                'gain' => $gain,
            ]);
        }

        $realizedPnl = BigDecimal::zero();
        foreach ($user->simulatedTransactions()->get(['realized_pnl']) as $transaction) {
            $realizedPnl = $realizedPnl->plus(BigDecimal::of($transaction->realized_pnl));
        }

        $allocationTotal = $portfolioValue->isZero() ? BigDecimal::one() : $portfolioValue;
        $allocation = collect($categoryValues)->map(fn (BigDecimal $value, string $category) => [
            'category' => str($category)->replace('_', ' ')->title()->toString(),
            'value' => (string) $value->toScale(2),
            'percentage' => (float) $value->dividedBy($allocationTotal, 4)->multipliedBy(100)->toScale(2),
        ])->values();

        $recentSimulatedTransactions = $user->simulatedTransactions()->with('asset')->latest('occurred_at')->limit(8)->get();
        $watchlist = $user->watchlistAssets()->where('is_active', true)->orderBy('name')->get();
        $watchlistQuotes = $watchlist->mapWithKeys(fn (InvestmentAsset $asset) => [$asset->id => $marketData->quote($asset)]);
        $marketOverview = InvestmentAsset::query()->whereIn('slug', ['bitcoin', 'ethereum', 'tesla', 'disney'])->get()
            ->map(fn (InvestmentAsset $asset) => ['asset' => $asset, 'quote' => $marketData->quote($asset)]);

        $portfolioChart = [
            'labels' => $positionRows->map(fn (array $row) => $row['position']->asset->symbol ?: $row['position']->asset->name)->all(),
            'costs' => $positionRows->map(fn (array $row) => (float) $row['position']->cost_basis)->all(),
            'values' => $positionRows->map(fn (array $row) => $row['value'] ? (float) (string) $row['value'] : null)->all(),
        ];

        return view('investor.dashboard', [
            'balances' => collect($balances)->map(fn (BigDecimal $amount, string $currency) => (string) $amount->toScale((int) config('platform.currency_precision.'.$currency, 2))),
            'recordCount' => $recordCount,
            'activity' => $activity,
            'activityTrend' => $activityTrend,
            'portfolioValue' => (string) $portfolioValue->toScale(2),
            'investedAmount' => (string) $investedAmount->toScale(2),
            'unrealizedPnl' => (string) $unrealizedPnl->toScale(2),
            'realizedPnl' => (string) $realizedPnl->toScale(2),
            'valuedCount' => $valuedCount,
            'unvaluedCount' => $unvaluedCount,
            'portfolioPositions' => $positionRows,
            'portfolioAllocation' => $allocation,
            'recentSimulatedTransactions' => $recentSimulatedTransactions,
            'watchlist' => $watchlist,
            'watchlistQuotes' => $watchlistQuotes,
            'marketOverview' => $marketOverview,
            'portfolioChart' => $portfolioChart,
        ]);
    }
}