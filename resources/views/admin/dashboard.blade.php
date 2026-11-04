@extends('layouts.admin')

@section('title', 'Command center | Commonwealth')
@section('description', 'Database-backed operational and financial overview for Commonwealth administrators.')

@section('content')
<section class="portal-header admin-command-header">
    <div class="container-xl">
        <p class="eyebrow dark-eyebrow"><span></span> COMMONWEALTH / COMMAND CENTER</p>
        <h1>Platform<br><em>overview.</em></h1>
        <p>Verified account and ledger activity. Live financial operations are {{ $liveOperationsEnabled ? 'enabled by configuration' : 'disabled' }}.</p>
    </div>
</section>

<section class="container-xl portal-wrap admin-command">
    <div class="admin-readiness-strip" role="status">
        <span class="admin-readiness-strip__mark" aria-hidden="true"></span>
        <div><strong>{{ $liveOperationsEnabled ? 'Live operations are enabled by configuration' : 'Pre-launch controls are active' }}</strong><p>Live financial operations: {{ $liveOperationsEnabled ? 'Enabled by configuration' : 'Disabled' }}. Financial totals below use posted records and remain separated by currency.</p></div>
    </div>

    @if($canViewLedger)
        <section class="admin-dashboard-section" aria-labelledby="financial-heading">
            <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> FINANCIAL OVERVIEW</p><h2 id="financial-heading">Ledger balances and activity</h2><p>Database-backed amounts in each recorded currency. Values are not converted or added together.</p></div></div>
            <div class="admin-metric-grid">
                <article class="admin-metric-card"><span>PLATFORM ASSETS</span><strong>{{ $assetTotals }}</strong><p>Active asset accounts net of posted entries.</p></article>
                <article class="admin-metric-card"><span>POSTED USER DEPOSITS</span><strong>{{ $depositTotals }}</strong><p>Posted credits to investor liability accounts.</p></article>
                <article class="admin-metric-card"><span>POSTED WITHDRAWALS</span><strong>{{ $withdrawalTotals }}</strong><p>Posted debits from investor liability accounts.</p></article>
                <article class="admin-metric-card"><span>RECORDED INCOME</span><strong>{{ $revenueTotals }}</strong><p>Net posted balances in income accounts.</p></article>
                <article class="admin-metric-card"><span>POSTED INVESTMENT VOLUME</span><strong>{{ $investmentTotals }}</strong><p>Non-demo investments linked to posted transactions.</p></article>
                <article class="admin-metric-card"><span>ACTUAL DISTRIBUTIONS</span><strong>{{ $distributionTotals }}</strong><p>Distribution records linked to posted transactions.</p></article>
                <article class="admin-metric-card"><span>POSTED TRANSACTIONS</span><strong>{{ number_format($postedTransactionCount) }}</strong><p>Immutable transactions in the ledger.</p></article>
                @if($canReviewDeposits)<article class="admin-metric-card admin-metric-card--queue"><span>UNREVIEWED DEPOSITS</span><strong>{{ number_format($pendingDepositCount) }}</strong><p>Requests without a recorded review or verification.</p></article>@endif
                @if($canReviewWithdrawals)<article class="admin-metric-card admin-metric-card--queue"><span>PENDING WITHDRAWALS</span><strong>{{ number_format($pendingWithdrawalCount) }}</strong><p>Requests currently marked pending.</p></article>@endif
            </div>
        </section>
    @endif

    @if($canViewUsers && $userStats)
        <section class="admin-dashboard-section" aria-labelledby="users-heading">
            <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> USER ACCOUNTS</p><h2 id="users-heading">Account activity</h2><p>Account totals from the user and role records.</p></div></div>
            <div class="admin-metric-grid">
                <article class="admin-metric-card"><span>REGISTERED USERS</span><strong>{{ number_format($userStats['registered']) }}</strong><p>All registered platform accounts.</p></article>
                <article class="admin-metric-card"><span>ACTIVE USERS</span><strong>{{ number_format($userStats['active']) }}</strong><p>Accounts currently allowed to sign in.</p></article>
                <article class="admin-metric-card"><span>EMAIL VERIFIED</span><strong>{{ number_format($userStats['verified']) }}</strong><p>Accounts with a verified email address.</p></article>
                <article class="admin-metric-card"><span>SUSPENDED USERS</span><strong>{{ number_format($userStats['suspended']) }}</strong><p>Accounts with access paused.</p></article>
                <article class="admin-metric-card"><span>INVESTOR ACCOUNTS</span><strong>{{ number_format($investorCount) }}</strong><p>Accounts assigned the investor role.</p></article>
                @if($canReviewKyc)<article class="admin-metric-card admin-metric-card--queue"><span>PENDING KYC</span><strong>{{ number_format($pendingKycCount) }}</strong><p>Submissions awaiting compliance review.</p></article>@endif
            </div>
        </section>
    @endif

    @if($canManageOpportunities || $canViewInvestmentReports)
        <section class="admin-dashboard-section" aria-labelledby="investments-heading">
            <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> INVESTMENT RECORDS</p><h2 id="investments-heading">Opportunities and subscriptions</h2><p>Non-demo records only. Funded records must have a linked posted ledger transaction.</p></div></div>
            <div class="admin-metric-grid">
                @if($canManageOpportunities)
                    <article class="admin-metric-card"><span>OPPORTUNITIES</span><strong>{{ number_format($opportunityCount) }}</strong><p>Non-demo catalog records.</p></article>
                    <article class="admin-metric-card"><span>PUBLISHED OPPORTUNITIES</span><strong>{{ number_format($publishedOpportunityCount) }}</strong><p>Reviewed, documented and investor-visible.</p></article>
                    <article class="admin-metric-card admin-metric-card--queue"><span>LEGAL REVIEW QUEUE</span><strong>{{ number_format($reviewQueueCount) }}</strong><p>Records not yet legally reviewed.</p></article>
                @endif
                @if($canViewInvestmentReports)
                    <article class="admin-metric-card"><span>ACTIVE INVESTMENTS</span><strong>{{ number_format($activeInvestmentCount) }}</strong><p>Active records linked to posted transactions.</p></article>
                    <article class="admin-metric-card"><span>COMPLETED INVESTMENTS</span><strong>{{ number_format($completedInvestmentCount) }}</strong><p>Completed records linked to posted transactions.</p></article>
                    <article class="admin-metric-card admin-metric-card--queue"><span>PENDING INVESTMENTS</span><strong>{{ number_format($pendingInvestmentCount) }}</strong><p>Investment records still marked pending.</p></article>
                    <article class="admin-metric-card"><span>MATURING IN 30 DAYS</span><strong>{{ number_format($maturingInvestmentCount) }}</strong><p>Active posted records with a scheduled maturity date.</p></article>
                    <article class="admin-metric-card"><span>POSTED DISTRIBUTIONS</span><strong>{{ number_format($postedDistributionCount) }}</strong><p>Records tied to posted ledger activity.</p></article>
                @endif
            </div>
        </section>
    @endif

    @if($canViewLedger)
        <section class="admin-dashboard-section" aria-labelledby="transactions-chart-heading">
            <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> FINANCIAL ACTIVITY</p><h2 id="transactions-chart-heading">Posted ledger activity</h2><p>Monthly counts by transaction type for the last six calendar months.</p></div></div>
            @if(collect($transactionTrend['datasets'])->sum(fn ($dataset) => array_sum($dataset['data'])) > 0)
                <div class="portal-panel admin-chart-panel"><div class="admin-dashboard-chart-wrap"><canvas data-admin-chart data-chart-kind="bar" data-labels="{{ implode('|', $transactionTrend['labels']) }}" data-series="{{ base64_encode(json_encode($transactionTrend['datasets'])) }}" role="img" aria-label="Monthly count of posted financial transactions"></canvas></div></div>
            @else
                <div class="portal-panel admin-empty-panel"><h3>No posted transaction activity</h3><p>No qualifying posted transactions were recorded in the last six months.</p></div>
            @endif
        </section>
    @endif

    @if($canReviewDeposits || $canReviewWithdrawals)
        <section class="admin-dashboard-section admin-dashboard-section--two">
            @if($canReviewDeposits)
                <article class="portal-panel admin-chart-panel">
                    <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> DEPOSIT REQUESTS</p><h2>Requests created</h2><p>Request count, not settled deposit volume.</p></div></div>
                    @if(array_sum($depositRequestTrend['datasets'][0]['data']) > 0)
                        <div class="admin-dashboard-chart-wrap admin-dashboard-chart-wrap--short"><canvas data-admin-chart data-chart-kind="line" data-labels="{{ implode('|', $depositRequestTrend['labels']) }}" data-series="{{ base64_encode(json_encode($depositRequestTrend['datasets'])) }}" role="img" aria-label="Monthly deposit request count"></canvas></div>
                    @else
                        <p class="admin-empty-note">No deposit requests were recorded in the last six months.</p>
                    @endif
                </article>
            @endif
            @if($canReviewWithdrawals)
                <article class="portal-panel admin-chart-panel">
                    <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> WITHDRAWAL REQUESTS</p><h2>Requests created</h2><p>Request count, not completed withdrawal volume.</p></div></div>
                    @if(array_sum($withdrawalRequestTrend['datasets'][0]['data']) > 0)
                        <div class="admin-dashboard-chart-wrap admin-dashboard-chart-wrap--short"><canvas data-admin-chart data-chart-kind="line" data-labels="{{ implode('|', $withdrawalRequestTrend['labels']) }}" data-series="{{ base64_encode(json_encode($withdrawalRequestTrend['datasets'])) }}" role="img" aria-label="Monthly withdrawal request count"></canvas></div>
                    @else
                        <p class="admin-empty-note">No withdrawal requests were recorded in the last six months.</p>
                    @endif
                </article>
            @endif
        </section>
    @endif

    @if($canViewInvestmentReports)
        <section class="admin-dashboard-section" aria-labelledby="investment-trend-heading">
            <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> INVESTMENT ACTIVITY</p><h2 id="investment-trend-heading">Funded investment records</h2><p>Monthly count of non-demo records linked to posted ledger transactions. It is not an investment return chart.</p></div></div>
            @if(array_sum($investmentTrend['datasets'][0]['data']) > 0)
                <div class="portal-panel admin-chart-panel"><div class="admin-dashboard-chart-wrap"><canvas data-admin-chart data-chart-kind="bar" data-labels="{{ implode('|', $investmentTrend['labels']) }}" data-series="{{ base64_encode(json_encode($investmentTrend['datasets'])) }}" role="img" aria-label="Monthly count of funded investment records"></canvas></div></div>
            @else
                <div class="portal-panel admin-empty-panel"><h3>No funded investments recorded</h3><p>Market-value growth is unavailable until a verified valuation source is connected.</p></div>
            @endif
        </section>
    @endif

    @if($canViewLedger)
        <section class="admin-dashboard-section admin-dashboard-section--two">
            <article class="portal-panel admin-currency-panel"><div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> REVENUE ANALYTICS</p><h2>Recorded income by currency</h2><p>Net posted balances in income accounts. Currency amounts are separate.</p></div></div><strong class="admin-currency-total">{{ $revenueTotals }}</strong></article>
            <article class="portal-panel admin-empty-panel"><p class="eyebrow dark-eyebrow"><span></span> ASSET PERFORMANCE</p><h2>Price comparisons unavailable</h2><p>No verified market-price or asset-valuation feed is connected. No stock, crypto, property or business performance is estimated here.</p></article>
        </section>
    @endif

    @if($canViewInvestorProfiles || $canViewInvestmentReports)
        <section class="admin-dashboard-section admin-dashboard-section--two">
            @if($canViewInvestorProfiles)
                <article class="portal-panel admin-record-panel">
                    <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> RECENT INVESTORS</p><h2>New investor accounts</h2><p>Recently created, non-demo investor records.</p></div><a class="quiet-link" href="{{ route('admin.investors.index') }}">Manage investors <span aria-hidden="true">&rarr;</span></a></div>
                    @if($recentRegistrations->isEmpty())<p class="admin-empty-note">No user accounts have been created yet.</p>
                    @else
                        <ul class="admin-record-list">@foreach($recentRegistrations as $registeredUser)
                            <li><span class="admin-record-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($registeredUser->name, 0, 1)) }}</span><span class="admin-record-copy"><strong>{{ $registeredUser->name }}</strong><small>{{ $registeredUser->role?->name ?? 'Role not assigned' }}</small></span><time datetime="{{ $registeredUser->created_at?->toAtomString() }}">{{ $registeredUser->created_at?->format('M j, Y') ?? 'Not available' }}</time></li>
                        @endforeach</ul>
                    @endif
                </article>
            @endif
            @if($canViewInvestmentReports)
                <article class="portal-panel admin-record-panel">
                    <div class="admin-dashboard-heading"><div><p class="eyebrow dark-eyebrow"><span></span> ADMINISTRATOR ACTIVITY</p><h2>Recent audit events</h2><p>Action names and actors from the audit log.</p></div></div>
                    @if($recentAdminActivities->isEmpty())<p class="admin-empty-note">No administrator activity has been logged yet.</p>
                    @else
                        <ul class="admin-record-list">@foreach($recentAdminActivities as $activity)
                            <li><span class="admin-activity-mark" aria-hidden="true"></span><span class="admin-record-copy"><strong>{{ str($activity->action)->replace(['.', '_'], ' ')->title() }}</strong><small>{{ $activity->actor?->name ?? 'System' }}</small></span><time datetime="{{ $activity->created_at?->toAtomString() }}">{{ $activity->created_at?->format('M j, Y') ?? 'Not available' }}</time></li>
                        @endforeach</ul>
                    @endif
                </article>
            @endif
        </section>
    @endif

    @if($canViewLedger)
        <section class="admin-dashboard-section">
            <div class="portal-panel admin-transactions-panel">
                <div class="admin-panel-heading"><div><p class="eyebrow dark-eyebrow"><span></span> FINANCIAL RECORDS</p><h2>Recent posted transactions</h2><p>Posted ledger records cannot be edited or deleted. Corrections require a new compensating transaction.</p></div><a class="button button-navy" href="{{ route('admin.transactions.index') }}">Open transaction history <span>→</span></a></div>
                @if($recentTransactions->isEmpty())<p class="portal-empty">No posted financial transactions are recorded yet.</p>
                @else
                    <div class="activity-table-wrap"><table class="activity-table admin-transaction-table">
                        <thead><tr><th scope="col">Reference</th><th scope="col">Type</th><th scope="col">Ledger lines</th><th scope="col">Posted</th><th scope="col">Status</th></tr></thead>
                        <tbody>@foreach($recentTransactions as $transaction)
                            <tr><td><a href="{{ route('admin.transactions.show', $transaction) }}"><strong>{{ $transaction->reference }}</strong></a><span>{{ $transaction->description ?: 'No description' }}</span></td><td>{{ str($transaction->transaction_type)->replace('_', ' ')->title() }}</td><td>{{ number_format($transaction->entries->count()) }}</td><td>{{ $transaction->posted_at?->format('M j, Y H:i') ?? 'Not available' }}</td><td><span class="transaction-status status-posted">Posted</span></td></tr>
                        @endforeach</tbody>
                    </table></div>
                @endif
            </div>
        </section>
    @endif

    <section class="admin-dashboard-section admin-dashboard-section--two">
        <article class="portal-panel admin-system-panel"><p class="eyebrow dark-eyebrow"><span></span> SYSTEM STATUS</p><h2>Pre-launch safeguards</h2><dl><div><dt>Application environment</dt><dd>{{ ucfirst(config('app.env')) }}</dd></div><div><dt>Live operations</dt><dd>{{ $liveOperationsEnabled ? 'Enabled' : 'Disabled' }}</dd></div><div><dt>Market data</dt><dd>Not connected</dd></div></dl></article>
        <article class="portal-panel admin-system-panel"><p class="eyebrow dark-eyebrow"><span></span> SECURITY MONITORING</p><h2>Alert sources</h2><p>No fraud-monitoring alert source is connected in this build. No alert count is fabricated.</p></article>
    </section>

    <p class="admin-dashboard-footnote">Commonwealth is not currently accepting live investments. Financial cards show recorded ledger values by currency.</p>
</section>
@endsection
