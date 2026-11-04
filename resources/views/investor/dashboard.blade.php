@extends('layouts.app')
@section('title', 'Investor dashboard | Commonwealth')
@section('content')
<section class="portal-header"><div class="container-xl"><p class="eyebrow dark-eyebrow"><span></span> INVESTOR PORTAL</p><h1>Your account,<br><em>in clear view.</em></h1><p>Verified account activity appears here from recorded ledger entries.</p></div></section>
<section class="container-xl portal-wrap">
    <div class="portal-summary">
        <article class="portal-card"><span>LEDGER BALANCE</span><h2>@forelse($balances as $currency => $amount){{ $currency }} {{ $amount }}@if(!$loop->last)<br>@endif @empty No posted balance @endforelse</h2><p>Calculated from posted ledger entries only.</p></article>
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
