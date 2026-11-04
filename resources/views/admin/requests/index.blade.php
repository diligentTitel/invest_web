@extends('layouts.admin')
@section('title', ($queueType === 'deposit' ? 'Deposit' : 'Withdrawal').' requests | Commonwealth')
@section('content')
@php($isDeposit = $queueType === 'deposit')
@php($label = $isDeposit ? 'Deposit' : 'Withdrawal')
@php($routeBase = $isDeposit ? 'admin.deposits' : 'admin.withdrawals')
<section class="portal-header"><div class="container-xl"><p class="eyebrow dark-eyebrow"><span></span> FINANCIAL OPERATIONS</p><h1>{{ $label }}<br><em>requests.</em></h1><p>Review stored request records and their current status.</p></div></section>
<section class="container-xl portal-wrap financial-queue">
    <div class="financial-queue__notice" role="note"><strong>Read-only review queue</strong><span>Payment verification, settlement, and fund movement are disabled until approved integrations are connected.</span></div>
    <div class="investor-toolbar">
        <a class="quiet-link" href="{{ route('admin.dashboard') }}">&larr; Back to administration</a>
        <form class="investor-filter" method="GET" action="{{ route($routeBase.'.index') }}">
            <label class="visually-hidden" for="request-search">Search requests</label><input id="request-search" class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, name, or email">
            <label class="visually-hidden" for="request-status">Filter by status</label><select id="request-status" class="form-control" name="status"><option value="">All statuses</option>
                @foreach($isDeposit ? ['created','payment_detected','verified','credited','rejected','expired'] : ['pending','approved','processing','completed','rejected','failed'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach
            </select><button class="button button-navy" type="submit">Apply filters</button>
            @if(($filters['search'] ?? null) || ($filters['status'] ?? null))<a class="filter-reset" href="{{ route($routeBase.'.index') }}">Clear</a>@endif
        </form>
    </div>
    <div class="portal-panel investor-list-panel">
        <div class="investor-list-summary"><strong>{{ number_format($items->total()) }}</strong> {{ strtolower($label) }} requests</div>
        @if($items->isEmpty())<div class="transaction-empty"><span class="investor-empty-mark" aria-hidden="true"></span><h2>No {{ strtolower($label) }} requests found</h2><p>Records will appear here when an enabled workflow creates a request.</p></div>
        @else
        <div class="activity-table-wrap"><table class="activity-table investor-table financial-queue__table"><thead><tr><th>Reference</th><th>Investor</th><th>Amount</th><th>Method</th><th>Status</th><th>Created</th><th><span class="visually-hidden">Details</span></th></tr></thead><tbody>
            @foreach($items as $item)
                                @php($currency = $isDeposit ? $item->currency_code : $item->currency)
                @php($method = $isDeposit ? trim(($item->cryptocurrency?->code ?? 'Payment').' / '.($item->network?->code ?? 'Network')) : ($item->network?->code ?? $item->account?->currency ?? 'Withdrawal'))
                <tr><td><strong>{{ $item->reference }}</strong></td><td><strong>{{ $item->user?->name ?? 'Account unavailable' }}</strong><span>{{ $item->user?->email }}</span></td><td><strong>{{ $item->display_amount }} {{ $currency }}</strong></td><td>{{ $method }}</td><td><span class="investor-access {{ in_array($item->status,['rejected','failed','expired'],true) ? 'investor-access--suspended' : '' }}">{{ str($item->status)->replace('_',' ')->title() }}</span></td><td>{{ $item->created_at?->format('M j, Y') ?? 'Not available' }}</td><td><a class="investor-open-link" href="{{ route($routeBase.'.show',$item) }}">Review record <span aria-hidden="true">&rarr;</span></a></td></tr>
            @endforeach
        </tbody></table></div><div class="catalog-pagination">{{ $items->links() }}</div>
        @endif
    </div>
</section>
@endsection
