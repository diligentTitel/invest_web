@extends('layouts.app')
@section('title', 'Investor dashboard | Commonwealth')
@section('content')
<section class="portal-header"><div class="container-xl"><p class="eyebrow dark-eyebrow"><span></span> INVESTOR PORTAL</p><h1>Your account,<br><em>in clear view.</em></h1><p>Explore a simulated multi-asset portfolio alongside your separately recorded ledger activity.</p></div></section>
<section class="container-xl portal-wrap">
    <div class="portfolio-heading"><div><p class="eyebrow dark-eyebrow"><span></span> HYPOTHETICAL PORTFOLIO</p><h2>Multi-asset overview</h2><p>Simulated holdings are separate from investments, cash balances, and posted ledger entries.</p></div><a class="button button-navy" href="{{ route('investments.index') }}">Explore assets <span>↗</span></a></div>
    <div class="portfolio-summary">
        <article class="portal-card"><span>VALUED PORTFOLIO</span><h2>USD {{ $portfolioValue }}</h2><p>{{ $unvaluedCount ? 'Partial valuation · '.$unvaluedCount.' holding(s) need a price' : 'Current value from available market quotes and your estimates.' }}</p></article>
        <article class="portal-card"><span>INVESTED AMOUNT</span><h2>USD {{ $investedAmount }}</h2><p>Remaining average-cost basis, including recorded purchase fees.</p></article>
        <article class="portal-card"><span>UNREALIZED P&amp;L</span><h2 class="{{ (float) $unrealizedPnl >= 0 ? 'amount-positive' : 'amount-negative' }}">USD {{ $unrealizedPnl }}</h2><p>Calculated only for holdings with an available valuation.</p></article>
        <article class="portal-card"><span>REALIZED P&amp;L</span><h2 class="{{ (float) $realizedPnl >= 0 ? 'amount-positive' : 'amount-negative' }}">USD {{ $realizedPnl }}</h2><p>Simulated sale proceeds less average-cost basis and fees.</p></article>
    </div>
    @if($unvaluedCount)<p class="valuation-warning">{{ $unvaluedCount }} holding(s) are excluded from valuation and unrealized P&amp;L because no market quote or manual estimate is available.</p>@endif
    <div class="portfolio-insights">
        <section class="portal-panel"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> PORTFOLIO PERFORMANCE</p><h2>Cost basis vs. current value</h2><p>Current comparison, not a historical return series. Unpriced holdings are shown without a value.</p></div></div>
            @if($portfolioPositions->isEmpty())<p class="portal-empty">Record a hypothetical transaction to start tracking positions.</p>@else<div class="dashboard-chart-wrap"><canvas data-portfolio-chart data-series="{{ base64_encode(json_encode([['label' => 'Cost basis', 'data' => $portfolioChart['costs']], ['label' => 'Current value', 'data' => $portfolioChart['values']]])) }}" data-labels="{{ implode('|', $portfolioChart['labels']) }}" aria-label="Portfolio cost basis compared with current value by asset" role="img"></canvas></div>@endif
        </section>
        <section class="portal-panel"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> DIVERSIFICATION</p><h2>Asset allocation</h2></div></div>
            @forelse($portfolioAllocation as $item)<div class="allocation-row"><div><span>{{ $item['category'] }}</span><strong>USD {{ $item['value'] }}</strong></div><div class="allocation-track"><i data-allocation-width="{{ min(100, max(0, $item['percentage'])) }}"></i></div><small>{{ $item['percentage'] }}%</small></div>@empty<p class="portal-empty">Allocation appears when holdings have a current valuation.</p>@endforelse
        </section>
    </div>
    <section class="portal-panel portfolio-holdings"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> POSITIONS</p><h2>Your holdings</h2></div></div>
        @if($portfolioPositions->isEmpty())<p class="portal-empty">No hypothetical holdings yet. Browse the catalog to add an asset.</p>@else
        <div class="activity-table-wrap"><table class="activity-table"><thead><tr><th>Asset</th><th>Category</th><th>Quantity</th><th>Cost basis</th><th>Current value</th><th>Unrealized P&amp;L</th><th>Valuation source</th></tr></thead><tbody>
            @foreach($portfolioPositions as $row)@php($position = $row['position'])<tr><td><strong><a href="{{ route('investments.show', $position->asset->slug) }}">{{ $position->asset->name }}</a></strong><span>{{ $position->asset->symbol ?? str($position->asset->asset_type)->replace('_', ' ')->title() }}</span></td><td>{{ str($position->asset->category)->replace('_', ' ')->title() }}</td><td>{{ $position->quantity }} {{ $position->asset->details['unit_label'] ?? 'units' }}</td><td>USD {{ number_format((float) $position->cost_basis, 2) }}</td><td>{{ $row['value'] ? 'USD '.number_format((float) (string) $row['value'], 2) : 'Not available' }}</td><td class="{{ $row['gain'] && $row['gain']->isNegative() ? 'amount-negative' : 'amount-positive' }}">{{ $row['gain'] ? 'USD '.number_format((float) (string) $row['gain'], 2) : 'Not available' }}</td><td>{{ $row['quote']['source'] ?? 'Unavailable' }}</td></tr>@endforeach
        </tbody></table></div>@endif
    </section>
    <div class="portfolio-insights">
        <section class="portal-panel"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> HYPOTHETICAL ACTIVITY</p><h2>Recent transactions</h2></div></div>
            @if($recentSimulatedTransactions->isEmpty())<p class="portal-empty">Simulated transactions will appear here.</p>@else<div class="activity-table-wrap"><table class="activity-table"><thead><tr><th>Asset</th><th>Action</th><th>Date</th><th>Quantity</th><th>Unit price</th><th>Realized P&amp;L</th></tr></thead><tbody>@foreach($recentSimulatedTransactions as $transaction)<tr><td><strong>{{ $transaction->asset->name }}</strong><span>{{ $transaction->asset->symbol ?? str($transaction->asset->category)->replace('_', ' ') }}</span></td><td>{{ ucfirst($transaction->side) }} · simulated</td><td>{{ $transaction->occurred_at->format('M j, Y') }}</td><td>{{ $transaction->quantity }}</td><td>USD {{ number_format((float) $transaction->unit_price, 4) }}</td><td>{{ $transaction->side === 'sell' ? 'USD '.number_format((float) $transaction->realized_pnl, 2) : '—' }}</td></tr>@endforeach</tbody></table></div>@endif
        </section>
        <section class="portal-panel"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> WATCHLIST</p><h2>Saved assets</h2></div></div>
            @forelse($watchlist as $asset)@php($quote = $watchlistQuotes[$asset->id] ?? null)<a class="watchlist-row" href="{{ route('investments.show', $asset->slug) }}"><span><strong>{{ $asset->name }}</strong><small>{{ $asset->symbol ?? str($asset->category)->replace('_', ' ')->title() }}</small></span><span>{{ $quote ? 'USD '.number_format($quote['price'], 2) : 'Price unavailable' }}</span></a>@empty<p class="portal-empty">Save assets from the explorer to follow them here.</p>@endforelse
        </section>
    </div>
    <section class="portal-panel market-overview"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> MARKET OVERVIEW</p><h2>Selected market assets</h2><p>Quotes are the latest values returned by each configured provider, not an offer to trade.</p></div></div><div class="market-overview-grid">@foreach($marketOverview as $item)<a class="market-overview-item" href="{{ route('investments.show', $item['asset']->slug) }}"><span>{{ $item['asset']->symbol }}</span><strong>{{ $item['quote'] ? 'USD '.number_format($item['quote']['price'], $item['asset']->category === 'cryptocurrency' ? 4 : 2) : 'Unavailable' }}</strong><small>{{ $item['quote']['source'] ?? 'Provider not configured' }}</small></a>@endforeach</div></section>

    <div class="portal-summary">
        <article class="portal-card"><span>LEDGER BALANCE</span><h2>@forelse($balances as $currency => $amount){{ $currency }} {{ $amount }}@if(!$loop->last)<br>@endif @empty No posted balance @endforelse</h2><p>Calculated from posted ledger entries only. Separate from hypothetical positions.</p></article>
        <article class="portal-card"><span>POSTED LEDGER ENTRIES</span><h2>{{ number_format($recordCount) }}</h2><p>Entries linked to this account.</p></article>
        <article class="portal-card portal-note"><span>ACCOUNT STATUS</span><h2>{{ auth()->user()->status === 'active' ? 'Active' : 'Under review' }}</h2><p>Email verified {{ auth()->user()->email_verified_at?->toFormattedDateString() ?? 'Not available' }}.</p></article>
    </div>
    <div class="portal-panel dashboard-trend-panel">
        <div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> ACCOUNT ACTIVITY</p><h2>Posted ledger entries</h2><p>Monthly count of entries posted to your accounts. This chart shows activity volume, not investment performance.</p></div></div>
        @if(array_sum($activityTrend['values']) > 0)
            <div class="dashboard-chart-wrap"><canvas id="activity-trend-chart" aria-label="Monthly count of posted account ledger entries" role="img" data-labels="{{ implode('|', $activityTrend['labels']) }}" data-values="{{ implode(',', $activityTrend['values']) }}"></canvas></div>
        @else
            <p class="portal-empty dashboard-chart-empty">No posted activity in the last six months. Account activity will appear here when ledger records post.</p>
        @endif
    </div>
    <div class="portal-content">
        <div class="portal-panel"><div class="panel-head"><div><p class="eyebrow dark-eyebrow"><span></span> ACTIVITY</p><h2>Recent posted activity</h2></div></div>
            @if($activity->isEmpty())
                <p class="portal-empty">No posted ledger activity is recorded for this account yet.</p>
            @else
                <div class="activity-table-wrap">
                    <table class="activity-table">
                        <thead><tr><th scope="col">Reference</th><th scope="col">Activity</th><th scope="col">Date</th><th scope="col">Amount</th></tr></thead>
                        <tbody>
                        @foreach($activity as $entry)
                            @php
                                $creditNormal = in_array($entry->account->account_type, ['liability', 'equity', 'income'], true);
                                $isIncrease = $creditNormal ? $entry->direction === 'credit' : $entry->direction === 'debit';
                            @endphp
                            <tr>
                                <td><strong>{{ $entry->transaction->reference }}</strong><span>{{ str($entry->transaction->transaction_type)->replace('_', ' ')->title() }}</span></td>
                                <td>{{ $entry->transaction->description ?: 'Posted ledger entry' }}</td>
                                <td>{{ $entry->transaction->posted_at?->format('M j, Y') ?? $entry->created_at?->format('M j, Y') ?? 'Not available' }}</td>
                                <td class="{{ $isIncrease ? 'amount-positive' : 'amount-negative' }}">{{ $isIncrease ? '+' : '−' }}{{ $entry->currency }} {{ $entry->display_amount }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <aside class="portal-panel portal-side"><p class="eyebrow dark-eyebrow"><span></span> NEXT STEPS</p><h2>Review before you invest.</h2><p>Read opportunity terms and their supporting documents. The platform is not currently accepting live deposits or investments.</p><a class="button button-navy" href="{{ route('opportunities.index') }}">Explore catalog <span>↗</span></a></aside>
    </div>
</section>
@endsection
