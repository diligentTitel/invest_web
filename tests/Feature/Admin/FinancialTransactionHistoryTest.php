<?php

namespace Tests\Feature\Admin;

use App\Domain\Ledger\LedgerPostingService;
use App\Models\FinancialAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrator_can_review_posted_transaction_and_ledger_lines(): void
    {
        $this->seed();
        $role = Role::query()->where('slug', 'super_administrator')->firstOrFail();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->forceFill(['role_id' => $role->id])->save();
        $admin->refresh();

        $cash = FinancialAccount::query()->create([
            'account_code' => 'platform-cash-ledger-test',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $investorAccount = FinancialAccount::query()->create([
            'account_code' => 'investor-ledger-test',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        $transaction = app(LedgerPostingService::class)->post('DEP-ADMIN-001', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '250.50', 'currency' => 'USD'],
            ['account_id' => $investorAccount->id, 'direction' => 'credit', 'amount' => '250.50', 'currency' => 'USD'],
        ], actorId: $admin->id, description: 'Recorded deposit for review');

        $this->assertTrue($admin->hasPermission('ledger.view'));
        $this->assertSame('active', $admin->status);
        $this->actingAs($admin)
            ->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertSee('DEP-ADMIN-001')
            ->assertSee('Recorded deposit for review');

        $this->get(route('admin.transactions.show', $transaction))
            ->assertOk()
            ->assertSee('platform-cash-ledger-test')
            ->assertSee('investor-ledger-test')
            ->assertSee('250.50')
            ->assertSee('Posted transaction records are immutable.');
    }

    public function test_investor_cannot_open_admin_transaction_history(): void
    {
        $this->seed();
        $role = Role::query()->where('slug', 'investor')->firstOrFail();
        $investor = User::factory()->create(['email_verified_at' => now()]);
        $investor->forceFill(['role_id' => $role->id])->save();

        $this->actingAs($investor)->get(route('admin.transactions.index'))->assertForbidden();
    }
}