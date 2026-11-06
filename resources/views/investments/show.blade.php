@extends('layouts.app')
@section('title', $asset->name.' | Investment profile')
@section('content')
<section class="page-hero"><div class="container-xl"><p class="eyebrow"><span></span> {{ str($asset->category)->replace('_', ' ')->upper() }} · {{ str($asset->asset_type)->replace('_', ' ')->title() }}</p><h1>{{ $asset->name }}@if($asset->symbol) <em>{{ $asset->symbol }}</em>@endif</h1><p>{{ $asset->description }}</p></div></section>
<section class="container-xl detail-wrap asset-detail-wrap">
    <div class="detail-main">
        @if($quote)
            <div class="quote-heading"><div><p class="eyebrow dark-eyebrow"><span></span> {{ $quote['source'] }} · {{ $quote['source'] === 'User-entered estimate' ? 'MANUAL VALUATION' : 'LATEST AVAILABLE' }}</p><h2>{{ $asset->currency }} {{ number_format($quote['price'], $asset->category === 'cryptocurrency' ? 4 : 2) }}</h2></div>@if(isset($quote['change_percent']))<span class="quote-change {{ $quote['change_percent'] >= 0 ? 'amount-positive' : 'amount-negative' }}">{{ $quote['change_percent'] >= 0 ? '+' : '' }}{{ number_format($quote['change_percent'], 2) }}%</span>@endif</div>
            <p class="quote-timestamp">{{ $quote['source'] === 'User-entered estimate' ? 'Entered' : 'Updated' }} {{ $quote['updated_at']?->format('M j, Y H:i') ?? 'time not provided' }} · Source: {{ $quote['source'] }}. Data may be delayed or unavailable.</p>
            @if(in_array($asset->category, ['public_equity', 'cryptocurrency'], true))
                <dl class="market-metrics"><div><dt>Market capitalization</dt><dd>{{ is_numeric($quote['market_cap'] ?? null) ? $asset->currency.' '.number_format($quote['market_cap'], 0) : 'Not available' }}</dd></div><div><dt>Trading volume</dt><dd>{{ is_numeric($quote['volume'] ?? null) ? number_format($quote['volume'], 0).' '.($quote['volume_unit'] ?? $asset->currency) : 'Not available' }}</dd></div></dl>
            @endif
        @else
            <div class="quote-unavailable"><p class="eyebrow dark-eyebrow"><span></span> VALUATION NOT AVAILABLE</p><h2>No verified market price</h2><p>{{ in_array($asset->category, ['public_equity', 'cryptocurrency'], true) ? 'The configured market-data provider did not return a current value. No sample price is shown.' : 'This asset type does not have a public market quote. Add your own clearly labeled estimate to a hypothetical holding.' }}</p></div>
        @endif

        @if($quote && count($quote['history'] ?? []))
            <div class="detail-block asset-history-block"><h3>Historical price · 6 months</h3><div class="asset-chart-wrap"><canvas id="asset-history-chart" data-points="{{ base64_encode(json_encode($quote['history'])) }}" aria-label="Historical price for {{ $asset->name }} over six months" role="img"></canvas></div><p>Historical data from {{ $quote['source'] }}. Past performance is not a forecast.</p></div>
        @endif

        @if(!empty($asset->details['financial_fields']))
            <div class="detail-block"><h3>Financial information</h3><p>Any figures shown are your own estimates, not verified company or property data.</p><dl class="profile-financials">@foreach($asset->details['financial_fields'] as $field)@php($metricKey = Illuminate\Support\Str::snake($field))@php($manualMetric = $position?->manual_metrics[$metricKey] ?? null)<div><dt>{{ $field }}</dt><dd>@if(is_numeric($manualMetric)){{ in_array($metricKey, ['occupancy', 'ownership_stake'], true) ? number_format((float) $manualMetric, 2).'%' : (in_array($metricKey, ['acres', 'machine_count'], true) ? number_format((float) $manualMetric, 2).' '.($metricKey === 'acres' ? 'acres' : 'machines') : (str_ends_with($metricKey, '_months') ? number_format((float) $manualMetric, 0).' months' : $asset->currency.' '.number_format((float) $manualMetric, 2))) }} <small>User-entered estimate</small>@else Not provided @endif</dd></div>@endforeach</dl></div>
        @endif

        <div class="detail-block"><h3>Profile classification</h3><dl class="profile-financials"><div><dt>Asset class</dt><dd>{{ str($asset->category)->replace('_', ' ')->title() }}</dd></div><div><dt>Instrument type</dt><dd>{{ str($asset->asset_type)->replace('_', ' ')->title() }}</dd></div><div><dt>Unit tracked</dt><dd>{{ $asset->details['unit_label'] ?? 'units' }}</dd></div></dl></div>
    </div>
    <aside class="detail-aside asset-trade-panel">
        <p class="eyebrow dark-eyebrow"><span></span> HYPOTHETICAL PORTFOLIO</p>
        <h2>Track a position</h2>
        <p>Transactions are simulations only. They do not place an order, create ownership, or affect your ledger balance.</p>
        @auth
            @if(auth()->user()->hasPermission('dashboard.view'))
                <form method="POST" action="{{ route('portfolio.transactions.store', $asset->slug) }}" class="sim-trade-form">
                    @csrf
                    <label>Action<select class="form-control" name="side"><option value="buy">Hypothetical buy</option><option value="sell">Hypothetical sell</option></select></label>
                    <label>Quantity ({{ $asset->details['unit_label'] ?? 'units' }})<input class="form-control" type="number" name="quantity" step="0.000000000000000001" min="0.000000000000000001" required value="{{ old('quantity') }}"></label>
                    <label>Hypothetical unit price ({{ $asset->currency }})<input class="form-control" type="number" name="unit_price" step="0.000000000000000001" min="0.000000000000000001" required value="{{ old('unit_price', $quote['price'] ?? '') }}"></label>
                    <label>Fees ({{ $asset->currency }})<input class="form-control" type="number" name="fees" step="0.000000000000000001" min="0" value="{{ old('fees', '0') }}"></label>
                    @if($errors->any())<p class="field-error">{{ $errors->first() }}</p>@endif
                    <button class="button button-navy w-100" type="submit">Record hypothetical transaction</button>
                </form>
                @if($position && $position->hasQuantity())
                    <div class="current-position"><span>Current quantity</span><strong>{{ $position->quantity }} {{ $asset->details['unit_label'] ?? 'units' }}</strong><span>Average acquisition cost</span><strong>{{ $asset->currency }} {{ number_format((float) $position->average_cost, 4) }}</strong></div>
                @endif
                @if($position)
                    @unless($position->hasQuantity())<form method="POST" action="{{ route('portfolio.positions.destroy', $asset->slug) }}">@csrf @method('DELETE')<button class="button button-outline w-100" type="submit">Remove closed position</button></form>@endunless
                @endif
                <form method="POST" action="{{ route('portfolio.valuation.update', $asset->slug) }}" class="manual-valuation-form">@csrf @method('PUT')<label>Manual unit valuation estimate<input class="form-control" type="number" name="manual_price" step="0.000000000000000001" min="0.000000000000000001" value="{{ old('manual_price', $position?->manual_price) }}"></label><p>Used only when a provider quote is unavailable.</p>@foreach($asset->details['financial_fields'] ?? [] as $field)@php($metricKey = Illuminate\Support\Str::snake($field))<label>{{ $field }} (your estimate)<input class="form-control" type="number" name="metrics[{{ $metricKey }}]" step="0.000000000000000001" min="0" value="{{ old('metrics.'.$metricKey, $position?->manual_metrics[$metricKey] ?? '') }}"></label>@endforeach<button class="button button-outline w-100" type="submit">Save estimates</button></form>
                <form method="POST" action="{{ route($isWatchlisted ? 'watchlist.destroy' : 'watchlist.store', $asset->slug) }}">@csrf @if($isWatchlisted)@method('DELETE')@endif<button class="button button-outline w-100" type="submit">{{ $isWatchlisted ? 'Remove from watchlist' : 'Add to watchlist' }}</button></form>
                <a class="asset-dashboard-link" href="{{ route('investor.dashboard') }}">View portfolio dashboard ↗</a>
            @else
                <p class="field-hint">Portfolio tracking is not enabled for this account.</p>
            @endif
        @else
            <a class="button button-navy w-100" href="{{ route('login') }}">Sign in to track a position</a>
        @endauth
    </aside>
</section>
@endsection