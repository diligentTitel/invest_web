<?php

namespace Tests\Feature\Auth;

use App\Domain\Ledger\LedgerPostingService;
use App\Models\FinancialAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_investor_can_sign_in_and_open_their_dashboard(): void
    {
        $this->seed();
        $investorRole = Role::query()->where('slug', 'investor')->firstOrFail();
        $user = User::factory()->create([
            'email' => 'verified@example.test',
            'email_verified_at' => now(),
        ]);
        $user->forceFill(['role_id' => $investorRole->id])->save();
        $cash = FinancialAccount::query()->create([
            'account_code' => 'platform-cash-usd',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $account = FinancialAccount::query()->create([
            'user_id' => $user->id,
            'account_code' => 'investor-'.$user->id.'-usd',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        app(LedgerPostingService::class)->post('DEP-TEST-100', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '125.25', 'currency' => 'USD'],
            ['account_id' => $account->id, 'direction' => 'credit', 'amount' => '125.25', 'currency' => 'USD'],
        ], description: 'Verified deposit');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('investor.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('investor.dashboard'))->assertOk()->assertSee('LEDGER BALANCE')->assertSee('DEP-TEST-100')->assertSee('Verified deposit')->assertSee('USD 125.25');
    }

    public function test_administrator_sign_in_opens_the_admin_dashboard(): void
    {
        $this->seed();
        $administratorRole = Role::query()->where('slug', 'administrator')->firstOrFail();
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'email_verified_at' => now(),
        ]);
        $user->forceFill(['role_id' => $administratorRole->id])->save();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('ADMINISTRATION');
    }
    public function test_investor_cannot_open_administrator_dashboard(): void
    {
        $this->seed();
        $investorRole = Role::query()->where('slug', 'investor')->firstOrFail();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->forceFill(['role_id' => $investorRole->id])->save();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_unverified_investor_is_redirected_to_email_verification(): void
    {
        $this->seed();
        $investorRole = Role::query()->where('slug', 'investor')->firstOrFail();
        $user = User::factory()->create(['email_verified_at' => null]);
        $user->forceFill(['role_id' => $investorRole->id])->save();

        $this->actingAs($user)
            ->get(route('investor.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }
}