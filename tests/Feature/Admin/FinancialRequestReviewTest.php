<?php

namespace Tests\Feature\Admin;

use App\Models\BlockchainNetwork;
use App\Models\DepositRequest;
use App\Models\FinancialAccount;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SupportedCryptocurrency;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialRequestReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_review_real_deposit_and_withdrawal_records_without_exposing_payout_destination(): void
    {
        $this->seed();
        $admin = $this->userWithRole('super_administrator', ['name' => 'Finance Admin']);
        $investor = $this->userWithRole('investor', ['name' => 'Morgan Lee', 'email' => 'morgan@example.test']);
        $network = BlockchainNetwork::query()->create(['code' => 'BTC-MAIN', 'name' => 'Bitcoin mainnet']);
        $crypto = SupportedCryptocurrency::query()->create(['code' => 'TSTBTC', 'name' => 'Bitcoin']);
        $deposit = DepositRequest::query()->create([
            'user_id' => $investor->id,
            'cryptocurrency_id' => $crypto->id,
            'network_id' => $network->id,
            'reference' => 'DEP-REAL-001',
            'idempotency_key' => 'deposit-test-key-001',
            'method' => 'crypto',
            'requested_amount' => '100.00',
            'expected_crypto_amount' => '0.001',
            'currency_code' => 'USD',
        ]);
        $account = FinancialAccount::query()->create([
            'user_id' => $investor->id,
            'account_code' => 'investor-test-usd',
            'account_type' => 'liability',
            'currency' => 'USD',
            'status' => 'active',
        ]);
        $withdrawal = WithdrawalRequest::query()->create([
            'user_id' => $investor->id,
            'financial_account_id' => $account->id,
            'reference' => 'WDR-REAL-001',
            'idempotency_key' => 'withdrawal-test-key-001',
            'amount' => '25.00',
            'currency' => 'USD',
            'destination_encrypted' => 'private-wallet-destination',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.deposits.index'))
            ->assertOk()
            ->assertSee('DEP-REAL-001')
            ->assertSee('Morgan Lee');

        $this->get(route('admin.deposits.show', $deposit))
            ->assertOk()
            ->assertSee('Bitcoin mainnet')
            ->assertSee('Funds verified')
            ->assertSee('Read-only review');

        $this->get(route('admin.withdrawals.index'))
            ->assertOk()
            ->assertSee('WDR-REAL-001')
            ->assertSee('25.00 USD');

        $this->get(route('admin.withdrawals.show', $withdrawal))
            ->assertOk()
            ->assertSee('Encrypted payout data is not displayed.')
            ->assertSee('Withdrawal workflow')
            ->assertDontSee('private-wallet-destination');
    }

    public function test_investor_cannot_open_financial_request_review_queues(): void
    {
        $this->seed();
        $investor = $this->userWithRole('investor');

        $this->actingAs($investor)
            ->get(route('admin.deposits.index'))
            ->assertForbidden();
        $this->get(route('admin.withdrawals.index'))->assertForbidden();
    }

    private function userWithRole(string $slug, array $attributes = []): User
    {
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user = User::factory()->create($attributes);
        $user->forceFill(['role_id' => $role->id])->save();

        return $user->refresh();
    }
}
