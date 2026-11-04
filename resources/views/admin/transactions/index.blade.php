@extends('layouts.admin')
@section('title', 'Transaction history | Commonwealth Admin')
@section('content')
<section class="portal-header"><div class="container-xl"><p class="eyebrow dark-eyebrow"><span></span> FINANCIAL RECORDS</p><h1>Transaction<br><em>history.</em></h1><p>Searchable, read-only ledger transactions and their recorded debit and credit lines.</p></div></section>
<section class="container-xl portal-wrap">
    <div class="transaction-toolbar"><a class="quiet-link" href="{{ route('admin.dashboard') }}">← Back to administration</a><form class="transaction-filter" method="GET" action="{{ route('admin.transactions.index') }}">
        <label class="visually-hidden" for="transaction-search">Search transactions</label><input id="transaction-search" class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, type, or description">
        <label class="visually-hidden" for="transaction-status">Filter by status</label><select id="transaction-status" class="form-control" name="status"><option value="">All statuses</option>@foreach(['posted' => 'Posted', 'pending' => 'Pending', 'failed' => 'Failed', 'reversed' => 'Reversed'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
        <button class="button button-navy" type="submit">Filter</button>@if($filters)<a class="filter-reset" href="{{ route('admin.transactions.index') }}">Clear</a>@endif
    </form></div>
    <div class="portal-panel transaction-list-panel">
        <div class="transaction-count"><strong>{{ number_format($transactions->total()) }}</strong> transaction records</div>
        @if($transactions->isEmpty())
            <div class="transaction-empty"><span>◎</span><h2>No matching transactions</h2><p>Recorded ledger activity will appear here once a financial transaction is posted.</p></div>
        @else
            <div class="activity-table-wrap"><table class="activity-table admin-transaction-table transaction-history-table">
                <thead><tr><th scope="col">Reference / description</th><th scope="col">Type</th><th scope="col">Entry lines</th><th scope="col">Actor</th><th scope="col">Created</th><th scope="col">Status</th></tr></thead>
                <tbody>@foreach($transactions as $transaction)
                    <tr><td><a href="{{ route('admin.transactions.show', $transaction) }}"><strong>{{ $transaction->reference }}</strong></a><span>{{ $transaction->description ?: 'No description recorded' }}</span></td><td>{{ str($transaction->transaction_type)->replace('_', ' ')->title() }}</td><td>{{ number_format($transaction->entries->count()) }} lines</td><td>{{ $transaction->actor?->name ?? 'System' }}</td><td>{{ $transaction->posted_at?->format('M j, Y H:i') ?? $transaction->created_at?->format('M j, Y H:i') ?? 'Not available' }}</td><td><span class="transaction-status status-{{ $transaction->status }}">{{ str($transaction->status)->title() }}</span></td></tr>
                @endforeach</tbody>
            </table></div>
            <div class="catalog-pagination">{{ $transactions->links() }}</div>
        @endif
    </div>
</section>
@endsection