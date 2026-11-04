<?php

namespace Tests\Feature\Admin;

use App\Domain\Ledger\LedgerPostingService;
use App\Models\FinancialAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrator_sees_posted_financial_records_and_demo_users_are_excluded(): void
    {
        $this->seed();

        $investorRole = Role::query()->where('slug', 'investor')->firstOrFail();
        $adminRole = Role::query()->where('slug', 'super_administrator')->firstOrFail();

        $admin = User::factory()->create(['name' => 'Dashboard Admin']);
        $admin->forceFill(['role_id' => $adminRole->id])->save();
        $admin->refresh();

        $investor = User::factory()->create(['name' => 'Real Investor']);
        $investor->forceFill(['role_id' => $investorRole->id])->save();
        $investor->refresh();

        $demoInvestor = User::factory()->create(['name' => 'Demo Observer', 'is_demo' => true]);
        $demoInvestor->forceFill(['role_id' => $investorRole->id])->save();
        $demoInvestor->refresh();

        $cash = FinancialAccount::query()->create([
            'account_code' => 'dashboard-platform-cash',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $investorBalance = FinancialAccount::query()->create([
            'user_id' => $investor->id,
            'account_code' => 'dashboard-investor-balance',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        $demoBalance = FinancialAccount::query()->create([
            'user_id' => $demoInvestor->id,
            'account_code' => 'dashboard-demo-balance',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);

        $ledger = app(LedgerPostingService::class);
        $ledger->post('REAL-DEP-ADMIN-001', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '125.50', 'currency' => 'USD'],
            ['account_id' => $investorBalance->id, 'direction' => 'credit', 'amount' => '125.50', 'currency' => 'USD'],
        ], actorId: $admin->id, description: 'Verified ledger test transaction');

        $ledger->post('DEMO-DEP-ADMIN-001', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '900.00', 'currency' => 'USD'],
            ['account_id' => $demoBalance->id, 'direction' => 'credit', 'amount' => '900.00', 'currency' => 'USD'],
        ], actorId: $admin->id, description: 'Demo-only ledger record');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('POSTED USER DEPOSITS')
            ->assertSee('USD 125.50')
            ->assertDontSee('USD 900.00')
            ->assertSee('REAL-DEP-ADMIN-001')
            ->assertDontSee('DEMO-DEP-ADMIN-001')
            ->assertDontSee('Demo Observer')
            ->assertSee('INVESTOR ACCOUNTS</span><strong>1</strong>', false);
    }

    public function test_investor_cannot_open_administration_dashboard(): void
    {
        $this->seed();
        $role = Role::query()->where('slug', 'investor')->firstOrFail();
        $investor = User::factory()->create();
        $investor->forceFill(['role_id' => $role->id])->save();

        $this->actingAs($investor)->get(route('admin.dashboard'))->assertForbidden();
    }
}
