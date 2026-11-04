@extends('layouts.admin')

@section('title', $investor->name.' | Investor management')
@section('content')
<section class="portal-header">
    <div class="container-xl">
        <p class="eyebrow dark-eyebrow"><span></span> INVESTOR PROFILE</p>
        <h1>{{ $investor->investorProfile?->legal_name ?: $investor->name }}</h1>
        <p>{{ $investor->email }}</p>
    </div>
</section>

<section class="container-xl portal-wrap investor-management">
    <div class="investor-toolbar">
        <a class="quiet-link" href="{{ route('admin.investors.index') }}">&larr; Back to investors</a>
        <span class="investor-access investor-access--{{ $investor->status }}">{{ str($investor->status)->title() }} access</span>
    </div>

    <div class="investor-detail-grid">
        <article class="portal-panel investor-detail-panel">
            <p class="eyebrow dark-eyebrow"><span></span> ACCOUNT INFORMATION</p>
            <h2>Profile details</h2>
            <dl class="investor-detail-list">
                <div><dt>Account name</dt><dd>{{ $investor->name }}</dd></div>
                <div><dt>Legal name</dt><dd>{{ $investor->investorProfile?->legal_name ?: 'Not provided' }}</dd></div>
                <div><dt>Email</dt><dd>{{ $investor->email }}</dd></div>
                <div><dt>Email verification</dt><dd>{{ $investor->email_verified_at ? 'Verified '.$investor->email_verified_at->format('M j, Y') : 'Not verified' }}</dd></div>
                <div><dt>Country</dt><dd>{{ $investor->investorProfile?->country_code ?: 'Not provided' }}</dd></div>
                <div><dt>Preferred currency</dt><dd>{{ $investor->investorProfile?->preferred_currency ?: 'Not provided' }}</dd></div>
                <div><dt>Profile verification</dt><dd>{{ str($investor->investorProfile?->verification_status ?: 'not_started')->replace('_', ' ')->title() }}</dd></div>
                <div><dt>Account created</dt><dd>{{ $investor->created_at?->format('M j, Y H:i') ?? 'Not available' }}</dd></div>
                <div><dt>Last sign in</dt><dd>{{ $investor->last_login_at?->format('M j, Y H:i') ?? 'No sign-in recorded' }}</dd></div>
            </dl>
        </article>

        <div class="investor-detail-side">
            @if(auth()->user()->hasPermission('investors.manage'))
                <article class="portal-panel investor-access-panel">
                    <p class="eyebrow dark-eyebrow"><span></span> ACCOUNT ACCESS</p>
                    <h2>{{ $investor->status === 'active' ? 'Suspend access' : 'Restore access' }}</h2>
                    <p>{{ $investor->status === 'active' ? 'Suspension blocks future sign-ins and removes investor permissions from this account.' : 'Restoring access allows this investor to sign in again. Email and identity verification are not changed.' }}</p>
                    <form method="POST" action="{{ route('admin.investors.status', $investor) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $investor->status === 'active' ? 'suspended' : 'active' }}">
                        <label for="investor-status-reason">Reason for this change</label>
                        <textarea class="form-control" id="investor-status-reason" name="reason" rows="4" minlength="5" maxlength="500" required>{{ old('reason') }}</textarea>
                        @error('reason')<span class="investor-form-error">{{ $message }}</span>@enderror
                        @error('status')<span class="investor-form-error">{{ $message }}</span>@enderror
                        <button class="button {{ $investor->status === 'active' ? 'button-danger' : 'button-navy' }}" type="submit">{{ $investor->status === 'active' ? 'Suspend investor access' : 'Restore investor access' }}</button>
                    </form>
                    <p class="investor-audit-note">The change and reason are added to the immutable admin audit log.</p>
                </article>
            @endif

            @if($canReviewKyc)
                <article class="portal-panel investor-kyc-panel">
                    <p class="eyebrow dark-eyebrow"><span></span> COMPLIANCE STATUS</p>
                    <h2>KYC submissions</h2>
                    @if($kycSubmissions->isEmpty())
                        <p class="investor-muted-note">No KYC submissions are recorded for this account.</p>
                    @else
                        <ul class="investor-event-list">
                            @foreach($kycSubmissions as $submission)
                                <li>
                                    <span><strong>{{ str($submission->status)->replace('_', ' ')->title() }}</strong><small>{{ $submission->provider ?: 'Provider not recorded' }}</small></span>
                                    <time datetime="{{ $submission->submitted_at?->toAtomString() }}">{{ $submission->submitted_at?->format('M j, Y') ?? 'Date unavailable' }}</time>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endif
        </div>
    </div>

    @if($canViewAuditLog)
        <article class="portal-panel investor-audit-panel">
            <p class="eyebrow dark-eyebrow"><span></span> AUDIT HISTORY</p>
            <h2>Recent account actions</h2>
            @if($recentAuditEvents->isEmpty())
                <p class="investor-muted-note">No audit events are recorded for this account.</p>
            @else
                <ul class="investor-event-list">
                    @foreach($recentAuditEvents as $event)
                        <li>
                            <span><strong>{{ str($event->action)->replace(['.', '_'], ' ')->title() }}</strong><small>{{ $event->actor?->name ?? 'System' }}@if($event->properties['reason'] ?? null) &middot; {{ $event->properties['reason'] }}@endif</small></span>
                            <time datetime="{{ $event->created_at?->toAtomString() }}">{{ $event->created_at?->format('M j, Y H:i') ?? 'Date unavailable' }}</time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
    @endif
</section>
@endsection
