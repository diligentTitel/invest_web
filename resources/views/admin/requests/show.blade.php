@extends('layouts.admin')
@section('title', ($queueType === 'deposit' ? 'Deposit' : 'Withdrawal').' request | Commonwealth')
@section('content')
@php($isDeposit = $queueType === 'deposit')
@php($label = $isDeposit ? 'Deposit' : 'Withdrawal')
@php($routeBase = $isDeposit ? 'admin.deposits' : 'admin.withdrawals')
<section class="portal-header"><div class="container-xl"><p class="eyebrow dark-eyebrow"><span></span> FINANCIAL OPERATIONS / {{ strtoupper($label) }}</p><h1>Request<br><em>{{ $record->reference }}</em></h1><p>Stored request details and verification records for administrative review.</p></div></section>
<section class="container-xl portal-wrap financial-queue">
    <div class="investor-toolbar"><a class="quiet-link" href="{{ route($routeBase.'.index') }}">&larr; Back to {{ strtolower($label) }} requests</a>
        @if($canViewInvestors)<a class="investor-open-link" href="{{ route('admin.investors.show',$record->user) }}">Open investor profile <span aria-hidden="true">&rarr;</span></a>@endif
    </div>
    <div class="financial-queue__notice" role="note"><strong>Read-only review</strong><span>@if($isDeposit) Incoming funds cannot be verified or credited from this screen.@else Payout destinations remain encrypted; withdrawals cannot be approved or dispatched from this screen.@endif</span></div>
    <div class="investor-detail-grid">
        <article class="portal-panel investor-detail-panel"><p class="eyebrow dark-eyebrow"><span></span> REQUEST INFORMATION</p><h2>{{ $label }} details</h2>
            <dl class="investor-detail-list">
                <div><dt>Reference</dt><dd>{{ $record->reference }}</dd></div><div><dt>Investor</dt><dd>{{ $record->user?->name ?? 'Account unavailable' }}<br>{{ $record->user?->email }}</dd></div>
                <div><dt>Amount</dt><dd>{{ $record->display_amount }} {{ $isDeposit ? $record->currency_code : $record->currency }}</dd></div>
                @if($isDeposit)
                    <div><dt>Method</dt><dd>{{ str($record->method)->replace('_',' ')->title() }}</dd></div><div><dt>Asset</dt><dd>{{ $record->cryptocurrency?->name ?? 'Not recorded' }} ({{ $record->cryptocurrency?->code ?? 'N/A' }})</dd></div>
                    <div><dt>Network</dt><dd>{{ $record->network?->name ?? 'Not recorded' }}</dd></div><div><dt>Expected asset amount</dt><dd>{{ $record->expected_crypto_amount ?: 'Not recorded' }}</dd></div>
                    <div><dt>Received asset amount</dt><dd>{{ $record->received_amount ?: 'Not recorded' }}</dd></div><div><dt>Payment provider</dt><dd>{{ $record->provider?->display_name ?? 'Not recorded' }}</dd></div>
                    <div><dt>Payment detected</dt><dd>{{ $record->payment_detected_at?->format('M j, Y H:i') ?? 'Not detected' }}</dd></div><div><dt>Funds verified</dt><dd>{{ $record->verified_at?->format('M j, Y H:i') ?? 'Not verified' }}</dd></div>
                @else
                    <div><dt>Network</dt><dd>{{ $record->network?->name ?? 'Not recorded' }}</dd></div><div><dt>Source account</dt><dd>{{ $record->account?->account_code ?? 'Not recorded' }}</dd></div>
                    <div><dt>Destination</dt><dd>Encrypted payout data is not displayed.</dd></div><div><dt>Payment reference</dt><dd>{{ $record->payment_reference ?: 'Not assigned' }}</dd></div>
                    <div><dt>Ledger transaction</dt><dd>{{ $record->ledger_transaction_id ?: 'Not posted' }}</dd></div><div><dt>Reviewed by</dt><dd>{{ $record->reviewer?->name ?? 'Not reviewed' }}</dd></div>
                    <div><dt>Review time</dt><dd>{{ $record->reviewed_at?->format('M j, Y H:i') ?? 'Not reviewed' }}</dd></div><div><dt>Completed</dt><dd>{{ $record->completed_at?->format('M j, Y H:i') ?? 'Not completed' }}</dd></div>
                @endif
                <div><dt>Status</dt><dd>{{ str($record->status)->replace('_',' ')->title() }}</dd></div><div><dt>Created</dt><dd>{{ $record->created_at?->format('M j, Y H:i') ?? 'Not available' }}</dd></div>
            </dl>
        </article>
        @if($isDeposit)
            <article class="portal-panel investor-kyc-panel"><p class="eyebrow dark-eyebrow"><span></span> PAYMENT OBSERVATIONS</p><h2>Recorded payment events</h2>
                @if($record->transactions->isEmpty())<p class="investor-muted-note">No payment events are attached to this request.</p>
                @else<ul class="investor-event-list">@foreach($record->transactions as $payment)<li><span><strong>{{ str($payment->verification_status)->replace('_',' ')->title() }} / {{ $payment->currency_code }}</strong><small>{{ $payment->network?->name ?? 'Network not recorded' }} &middot; {{ $payment->observed_amount ?: 'Amount not recorded' }} &middot; {{ $payment->confirmations }} confirmations</small>@if($payment->transaction_hash)<small>Transaction hash: {{ $payment->transaction_hash }}</small>@endif</span><time>{{ $payment->observed_at?->format('M j, Y H:i') ?? 'Time unavailable' }}</time></li>@endforeach</ul>@endif
            </article>
        @else
            <article class="portal-panel investor-kyc-panel"><p class="eyebrow dark-eyebrow"><span></span> SETTLEMENT STATUS</p><h2>Withdrawal workflow</h2><p class="investor-muted-note">This view is for record inspection. Payout authorization, transfer, and ledger settlement are not enabled. No payout destination is disclosed here.</p>@if($record->rejection_reason)<dl class="investor-detail-list"><div><dt>Recorded review reason</dt><dd>{{ $record->rejection_reason }}</dd></div></dl>@endif</article>
        @endif
    </div>
</section>
@endsection
