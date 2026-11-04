<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DepositRequest;
use App\Models\FinancialTransaction;
use App\Models\Investment;
use App\Models\InvestmentDistribution;
use App\Models\InvestmentOpportunity;
use App\Models\KycSubmission;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Brick\Math\BigDecimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $actor = request()->user();
        $realUsers = User::query()->where('is_demo', false);
        $canViewLedger = $actor->hasPermission('ledger.view');
        $canViewUsers = $actor->hasPermission('investors.view') || $actor->hasPermission('investors.view-limited');
        $canViewInvestorProfiles = $actor->hasPermission('investors.view');
        $canReviewKyc = $actor->hasPermission('kyc.review');
        $canReviewDeposits = $actor->hasPermission('deposits.review');
        $canReviewWithdrawals = $actor->hasPermission('withdrawals.review');
        $canManageOpportunities = $actor->hasPermission('opportunities.manage');
        $canViewInvestmentReports = $actor->hasPermission('reports.view');

        $trendStart = now()->startOfMonth()->subMonths(5);
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $labels = $months->map(fn (Carbon $month) => $month->format('M Y'))->all();

        $postedRows = $canViewLedger
            ? FinancialTransaction::query()
                ->where('status', 'posted')
                ->whereDoesntHave('entries.account.user', fn ($query) => $query->where('is_demo', true))
                ->whereNotNull('posted_at')
                ->where('posted_at', '>=', $trendStart)
                ->get(['transaction_type', 'posted_at'])
            : collect();

        $transactionTrend = $this->transactionTrend($postedRows, $months);
        $userRegistrationTrend = $canViewUsers
            ? $this->countTrend(User::query()->where('is_demo', false)->where('created_at', '>=', $trendStart)->get(['created_at']), $months, 'created_at', 'New accounts')
            : ['labels' => $labels, 'datasets' => []];
        $depositRequestTrend = $canReviewDeposits
            ? $this->countTrend(DepositRequest::query()->whereHas('user', fn ($query) => $query->where('is_demo', false))->where('created_at', '>=', $trendStart)->get(['created_at']), $months, 'created_at', 'Deposit requests')
            : ['labels' => $labels, 'datasets' => []];
        $withdrawalRequestTrend = $canReviewWithdrawals
            ? $this->countTrend(WithdrawalRequest::query()->whereHas('user', fn ($query) => $query->where('is_demo', false))->where('created_at', '>=', $trendStart)->get(['created_at']), $months, 'created_at', 'Withdrawal requests')
            : ['labels' => $labels, 'datasets' => []];

        $investmentTrendRows = $canViewInvestmentReports
            ? DB::table('investments as investments')
                ->join('financial_transactions as transactions', 'transactions.id', '=', 'investments.ledger_transaction_id')
                ->join('investment_opportunities as opportunities', 'opportunities.id', '=', 'investments.investment_opportunity_id')
                ->join('users', 'users.id', '=', 'investments.user_id')
                ->where('users.is_demo', false)
                ->where('transactions.status', 'posted')
                ->where('opportunities.is_demo', false)
                ->where('transactions.posted_at', '>=', $trendStart)
                ->get(['transactions.posted_at'])
            : collect();
        $investmentTrend = $this->countTrend($investmentTrendRows, $months, 'posted_at', 'Funded investments');

        $recentTransactions = $canViewLedger
            ? FinancialTransaction::query()->where('status', 'posted')
                ->whereDoesntHave('entries.account.user', fn ($query) => $query->where('is_demo', true))
                ->with(['entries.account:id,account_code,account_type,currency'])
                ->latest('posted_at')->limit(6)->get()
            : collect();

        $recentRegistrations = $canViewInvestorProfiles
            ? User::query()->where('is_demo', false)->whereHas('role', fn ($query) => $query->where('slug', 'investor'))->with('role:id,name,slug')->latest('created_at')->limit(6)
                ->get(['id', 'name', 'role_id', 'created_at'])
            : collect();
        $recentAdminActivities = $canViewInvestmentReports
            ? AuditLog::query()->with('actor:id,name')->latest('created_at')->limit(6)->get()
            : collect();

        $opportunities = InvestmentOpportunity::query()->where('is_demo', false);
        $investments = Investment::query()
            ->whereHas('opportunity', fn ($query) => $query->where('is_demo', false))
            ->whereHas('user', fn ($query) => $query->where('is_demo', false));

        return view('admin.dashboard', [
            'canViewLedger' => $canViewLedger,
            'canViewUsers' => $canViewUsers,
            'canViewInvestorProfiles' => $canViewInvestorProfiles,
            'canReviewKyc' => $canReviewKyc,
            'canReviewDeposits' => $canReviewDeposits,
            'canReviewWithdrawals' => $canReviewWithdrawals,
            'canManageOpportunities' => $canManageOpportunities,
            'canViewInvestmentReports' => $canViewInvestmentReports,
            'liveOperationsEnabled' => (bool) config('platform.live_operations_enabled'),
            'investorCount' => $canViewUsers
                ? (clone $realUsers)->whereHas('role', fn ($query) => $query->where('slug', 'investor'))->count()
                : null,
            'userStats' => $canViewUsers ? [
                'registered' => (clone $realUsers)->count(),
                'active' => (clone $realUsers)->where('status', 'active')->count(),
                'verified' => (clone $realUsers)->whereNotNull('email_verified_at')->count(),
                'suspended' => (clone $realUsers)->where('status', 'suspended')->count(),
            ] : null,
            'pendingKycCount' => $canReviewKyc ? KycSubmission::query()->where('status', 'pending')->whereHas('user', fn ($query) => $query->where('is_demo', false))->count() : null,
            'reviewQueueCount' => $canManageOpportunities
                ? (clone $opportunities)->where('legal_review_status', 'not_reviewed')->count()
                : null,
            'opportunityCount' => $canManageOpportunities ? (clone $opportunities)->count() : null,
            'publishedOpportunityCount' => $canManageOpportunities
                ? (clone $opportunities)->where('availability_status', 'published')
                    ->where('legal_review_status', 'approved')->whereNotNull('published_at')
                    ->whereHas('documents', fn ($query) => $query->where('is_investor_visible', true))->count()
                : null,
            'pendingDepositCount' => $canReviewDeposits
                ? DepositRequest::query()->whereHas('user', fn ($query) => $query->where('is_demo', false))->whereNull('verified_at')->whereNull('reviewed_at')->count()
                : null,
            'pendingWithdrawalCount' => $canReviewWithdrawals
                ? WithdrawalRequest::query()->whereHas('user', fn ($query) => $query->where('is_demo', false))->where('status', 'pending')->count()
                : null,
            'pendingInvestmentCount' => $canViewInvestmentReports
                ? (clone $investments)->where('status', 'pending')->count()
                : null,
            'activeInvestmentCount' => $canViewInvestmentReports
                ? (clone $investments)->where('status', 'active')
                    ->whereHas('ledgerTransaction', fn ($query) => $query->where('status', 'posted'))
                    ->whereHas('user', fn ($query) => $query->where('is_demo', false))->count()
                : null,
            'completedInvestmentCount' => $canViewInvestmentReports
                ? (clone $investments)->where('status', 'completed')
                    ->whereHas('ledgerTransaction', fn ($query) => $query->where('status', 'posted'))
                    ->whereHas('user', fn ($query) => $query->where('is_demo', false))->count()
                : null,
            'maturingInvestmentCount' => $canViewInvestmentReports
                ? (clone $investments)->where('status', 'active')
                    ->whereBetween('matures_at', [now(), now()->addDays(30)])
                    ->whereHas('ledgerTransaction', fn ($query) => $query->where('status', 'posted'))
                    ->whereHas('user', fn ($query) => $query->where('is_demo', false))->count()
                : null,
            'postedDistributionCount' => $canViewInvestmentReports
                ? InvestmentDistribution::query()->whereNotNull('ledger_transaction_id')
                    ->whereHas('ledgerTransaction', fn ($query) => $query->where('status', 'posted'))
                    ->whereHas('investment.user', fn ($query) => $query->where('is_demo', false))
                    ->whereHas('investment.opportunity', fn ($query) => $query->where('is_demo', false))->count()
                : null,
            'postedTransactionCount' => $canViewLedger ? FinancialTransaction::query()->where('status', 'posted')->whereDoesntHave('entries.account.user', fn ($query) => $query->where('is_demo', true))->count() : null,
            'assetTotals' => $canViewLedger ? $this->formatCurrencyTotals($this->ledgerTotals('asset', 'debit')) : null,
            'depositTotals' => $canViewLedger ? $this->formatCurrencyTotals($this->ledgerTotals('liability', 'credit', 'deposit', true, 'credit')) : null,
            'withdrawalTotals' => $canViewLedger ? $this->formatCurrencyTotals($this->ledgerTotals('liability', 'credit', 'withdrawal', true, 'debit')) : null,
            'revenueTotals' => $canViewLedger ? $this->formatCurrencyTotals($this->ledgerTotals('income', 'credit')) : null,
            'investmentTotals' => $canViewInvestmentReports ? $this->formatCurrencyTotals($this->postedInvestmentTotals()) : null,
            'distributionTotals' => $canViewInvestmentReports ? $this->formatCurrencyTotals($this->postedDistributionTotals()) : null,
            'recentTransactions' => $recentTransactions,
            'recentRegistrations' => $recentRegistrations,
            'recentAdminActivities' => $recentAdminActivities,
            'transactionTrend' => $transactionTrend,
            'userRegistrationTrend' => $userRegistrationTrend,
            'depositRequestTrend' => $depositRequestTrend,
            'withdrawalRequestTrend' => $withdrawalRequestTrend,
            'investmentTrend' => $investmentTrend,
        ]);
    }

    private function ledgerTotals(string $accountType, string $normalSide, ?string $transactionType = null, bool $investorAccountsOnly = false, ?string $entryDirection = null): Collection
    {
        $query = DB::table('ledger_entries as entries')
            ->join('financial_accounts as accounts', 'accounts.id', '=', 'entries.financial_account_id')
            ->join('financial_transactions as transactions', 'transactions.id', '=', 'entries.financial_transaction_id')
            ->where('transactions.status', 'posted')
            ->where('accounts.status', 'active')
            ->where('accounts.account_type', $accountType)
            ->when($transactionType, fn ($query, string $type) => $query->where('transactions.transaction_type', $type))
            ->when($investorAccountsOnly, fn ($query) => $query->whereNotNull('accounts.user_id'))
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('ledger_entries as demo_entries')
                    ->join('financial_accounts as demo_accounts', 'demo_accounts.id', '=', 'demo_entries.financial_account_id')
                    ->join('users as demo_users', 'demo_users.id', '=', 'demo_accounts.user_id')
                    ->whereColumn('demo_entries.financial_transaction_id', 'transactions.id')
                    ->where('demo_users.is_demo', true);
            })
            ->when($entryDirection, fn ($query, string $direction) => $query->where('entries.direction', $direction));

        $sum = $entryDirection
            ? 'SUM(entries.amount)'
            : "SUM(CASE WHEN entries.direction = '{$normalSide}' THEN entries.amount ELSE entries.amount * -1 END)";

        return $query->select('entries.currency')->selectRaw($sum.' AS total')
            ->groupBy('entries.currency')->orderBy('entries.currency')->get();
    }

    private function postedInvestmentTotals(): Collection
    {
        return DB::table('investments as investments')
            ->join('financial_transactions as transactions', 'transactions.id', '=', 'investments.ledger_transaction_id')
            ->join('investment_opportunities as opportunities', 'opportunities.id', '=', 'investments.investment_opportunity_id')
            ->join('users', 'users.id', '=', 'investments.user_id')
            ->where('users.is_demo', false)
            ->where('transactions.status', 'posted')->where('opportunities.is_demo', false)
            ->select('investments.currency')->selectRaw('SUM(investments.principal_amount) AS total')
            ->groupBy('investments.currency')->orderBy('investments.currency')->get();
    }

    private function postedDistributionTotals(): Collection
    {
        return DB::table('investment_distributions as distributions')
            ->join('financial_transactions as transactions', 'transactions.id', '=', 'distributions.ledger_transaction_id')
            ->join('investments', 'investments.id', '=', 'distributions.investment_id')
            ->join('investment_opportunities as opportunities', 'opportunities.id', '=', 'investments.investment_opportunity_id')
            ->join('users', 'users.id', '=', 'investments.user_id')
            ->where('users.is_demo', false)->where('opportunities.is_demo', false)
            ->where('transactions.status', 'posted')
            ->select('distributions.currency')->selectRaw('SUM(distributions.amount) AS total')
            ->groupBy('distributions.currency')->orderBy('distributions.currency')->get();
    }

    private function formatCurrencyTotals(Collection $rows): string
    {
        if ($rows->isEmpty()) {
            return 'No posted records';
        }

        return $rows->map(function (object $row): string {
            $currency = strtoupper((string) $row->currency);
            $precision = (int) config('platform.currency_precision.'.$currency, 2);

            return $currency.' '.BigDecimal::of((string) $row->total)->toScale($precision);
        })->implode(' · ');
    }

    private function transactionTrend(Collection $rows, Collection $months): array
    {
        $types = [
            'deposit' => 'Posted deposits',
            'withdrawal' => 'Posted withdrawals',
            'investment' => 'Posted investments',
            'distribution' => 'Posted distributions',
        ];
        $datasets = [];

        foreach ($types as $type => $label) {
            $datasets[] = [
                'label' => $label,
                'data' => $months->map(fn (Carbon $month) => $rows->filter(
                    fn ($row) => $row->transaction_type === $type && $this->monthKey($row->posted_at) === $month->format('Y-m')
                )->count())->all(),
            ];
        }

        return ['labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->all(), 'datasets' => $datasets];
    }

    private function countTrend(Collection $rows, Collection $months, string $dateColumn, string $label): array
    {
        return [
            'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->all(),
            'datasets' => [[
                'label' => $label,
                'data' => $months->map(fn (Carbon $month) => $rows->filter(
                    fn ($row) => $this->monthKey(data_get($row, $dateColumn)) === $month->format('Y-m')
                )->count())->all(),
            ]],
        ];
    }

    private function monthKey(mixed $value): ?string
    {
        return $value === null ? null : Carbon::parse($value)->format('Y-m');
    }
}
