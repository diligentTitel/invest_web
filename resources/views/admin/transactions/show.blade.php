@extends('layouts.admin')
@section('title', 'Ledger transaction '.$transaction->reference.' | Commonwealth Admin')
@section('content')
<section class="portal-header"><div class="container-xl"><p class="eyebrow dark-eyebrow"><span></span> LEDGER ENTRY DETAIL</p><h1>{{ $transaction->reference }}</h1><p>{{ $transaction->description ?: 'No description recorded' }}</p></div></section>
<section class="container-xl portal-wrap">
    <div class="transaction-toolbar"><a class="quiet-link" href="{{ route('admin.transactions.index') }}">← Back to transaction history</a><span class="transaction-status status-{{ $transaction->status }}">{{ str($transaction->status)->title() }}</span></div>
    <div class="transaction-detail-grid">
        <article class="portal-panel transaction-meta"><p class="eyebrow dark-eyebrow"><span></span> TRANSACTION DETAILS</p><dl><dt>Reference</dt><dd>{{ $transaction->reference }}</dd><dt>Type</dt><dd>{{ str($transaction->transaction_type)->replace('_', ' ')->title() }}</dd><dt>Status</dt><dd>{{ str($transaction->status)->title() }}</dd><dt>Created</dt><dd>{{ $transaction->created_at?->format('M j, Y H:i:s') ?? 'Not available' }}</dd><dt>Posted</dt><dd>{{ $transaction->posted_at?->format('M j, Y H:i:s') ?? 'Not posted' }}</dd><dt>Recorded by</dt><dd>{{ $transaction->actor?->name ?? 'System' }}@if($transaction->actor) · {{ $transaction->actor->email }}@endif</dd></dl></article>
        <article class="portal-panel transaction-lines"><p class="eyebrow dark-eyebrow"><span></span> APPEND-ONLY LEDGER LINES</p><p class="ledger-help">Each currency must balance between debits and credits. Entries are displayed as recorded.</p>
            @if($transaction->entries->isEmpty())<p class="portal-empty">No ledger lines are attached to this transaction.</p>@else
                <div class="activity-table-wrap"><table class="activity-table"><thead><tr><th scope="col">Account</th><th scope="col">Direction</th><th scope="col">Currency</th><th scope="col">Amount</th></tr></thead><tbody>
                    @foreach($transaction->entries as $entry)<tr><td><strong>{{ $entry->account->account_code }}</strong><span>{{ str($entry->account->account_type)->title() }}</span></td><td><span class="ledger-direction direction-{{ $entry->direction }}">{{ str($entry->direction)->title() }}</span></td><td>{{ $entry->currency }}</td><td class="ledger-amount">{{ $entry->display_amount }}</td></tr>@endforeach
                </tbody></table></div>
            @endif
        </article>
    </div>
    <p class="ledger-immutable-note">Posted transaction records are immutable. Any correction requires a new, traceable compensating ledger transaction.</p>
</section>
@endsection