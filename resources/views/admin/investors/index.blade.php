@extends('layouts.admin')

@section('title', 'Investor management | Commonwealth Admin')
@section('content')
<section class="portal-header">
    <div class="container-xl">
        <p class="eyebrow dark-eyebrow"><span></span> USER MANAGEMENT</p>
        <h1>Investor<br><em>accounts.</em></h1>
        <p>Review real investor profiles and manage account access. Status changes are recorded in the audit log.</p>
    </div>
</section>

<section class="container-xl portal-wrap investor-management">
    <div class="investor-toolbar">
        <a class="quiet-link" href="{{ route('admin.dashboard') }}">&larr; Back to administration</a>
        <form class="investor-filter" method="GET" action="{{ route('admin.investors.index') }}">
            <label class="visually-hidden" for="investor-search">Search investors</label>
            <input id="investor-search" class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, legal name, or email">
            <label class="visually-hidden" for="investor-status">Filter by access status</label>
            <select id="investor-status" class="form-control" name="status">
                <option value="">All access statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Suspended</option>
            </select>
            <button class="button button-navy" type="submit">Apply filters</button>
            @if(($filters['search'] ?? null) || ($filters['status'] ?? null))
                <a class="filter-reset" href="{{ route('admin.investors.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="portal-panel investor-list-panel">
        <div class="investor-list-summary"><strong>{{ number_format($investors->total()) }}</strong> investor accounts</div>
        @if($investors->isEmpty())
            <div class="transaction-empty">
                <span class="investor-empty-mark" aria-hidden="true"></span>
                <h2>No matching investors</h2>
                <p>Real investor accounts will appear here when they register.</p>
            </div>
        @else
            <div class="activity-table-wrap">
                <table class="activity-table investor-table">
                    <thead><tr><th scope="col">Investor</th><th scope="col">Country</th><th scope="col">Verification</th><th scope="col">Access</th><th scope="col">Joined</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach($investors as $investor)
                            <tr>
                                <td><strong>{{ $investor->investorProfile?->legal_name ?: $investor->name }}</strong><span>{{ $investor->email }}</span></td>
                                <td>{{ $investor->investorProfile?->country_code ?: 'Not provided' }}</td>
                                <td>{{ str($investor->investorProfile?->verification_status ?: 'not_started')->replace('_', ' ')->title() }}</td>
                                <td><span class="investor-access investor-access--{{ $investor->status }}">{{ str($investor->status)->title() }}</span></td>
                                <td>{{ $investor->created_at?->format('M j, Y') ?? 'Not available' }}</td>
                                <td><a class="investor-open-link" href="{{ route('admin.investors.show', $investor) }}">View account <span aria-hidden="true">&rarr;</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="catalog-pagination">{{ $investors->links() }}</div>
        @endif
    </div>
</section>
@endsection
