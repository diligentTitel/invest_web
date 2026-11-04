<?php

namespace Tests\Unit\Domain\Ledger;

use App\Domain\Ledger\LedgerPostingService;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LedgerPostingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_posts_balanced_entries_and_returns_existing_idempotent_request(): void
    {
        $cash = FinancialAccount::query()->create([
            'account_code' => 'cash-usd',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $investor = FinancialAccount::query()->create([
            'account_code' => 'investor-1-usd',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        $entries = [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '125.25', 'currency' => 'USD'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '125.25', 'currency' => 'USD'],
        ];

        $service = app(LedgerPostingService::class);
        $first = $service->post('DEP-001', 'deposit', $entries, idempotencyKey: 'webhook-001');
        $second = $service->post('DEP-001', 'deposit', $entries, idempotencyKey: 'webhook-001');

        $this->assertSame('posted', $first->status);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(2, $first->entries->count());
        $this->assertSame(1, FinancialTransaction::query()->count());
    }

    public function test_it_rejects_unbalanced_entries(): void
    {
        $first = FinancialAccount::query()->create([
            'account_code' => 'cash-eur',
            'account_type' => 'asset',
            'currency' => 'EUR',
        ]);
        $second = FinancialAccount::query()->create([
            'account_code' => 'investor-2-eur',
            'account_type' => 'liability',
            'currency' => 'EUR',
        ]);

        $this->expectException(ValidationException::class);

        app(LedgerPostingService::class)->post('DEP-002', 'deposit', [
            ['account_id' => $first->id, 'direction' => 'debit', 'amount' => '12.00', 'currency' => 'EUR'],
            ['account_id' => $second->id, 'direction' => 'credit', 'amount' => '11.99', 'currency' => 'EUR'],
        ]);
    }

    public function test_it_reuses_the_same_reference_for_an_identical_request(): void
    {
        $cash = FinancialAccount::query()->create([
            'account_code' => 'cash-reference',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $investor = FinancialAccount::query()->create([
            'account_code' => 'investor-reference',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        $entries = [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '20.00', 'currency' => 'USD'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '20.00', 'currency' => 'USD'],
        ];

        $service = app(LedgerPostingService::class);
        $first = $service->post('DEP-003', 'deposit', $entries);
        $second = $service->post('DEP-003', 'deposit', $entries);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, FinancialTransaction::query()->count());
    }

    public function test_it_rejects_reuse_of_idempotency_key_with_different_amount(): void
    {
        $cash = FinancialAccount::query()->create([
            'account_code' => 'cash-idem',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $investor = FinancialAccount::query()->create([
            'account_code' => 'investor-idem',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        $service = app(LedgerPostingService::class);

        $service->post('DEP-004', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '20.00', 'currency' => 'USD'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '20.00', 'currency' => 'USD'],
        ], idempotencyKey: 'same-key');

        $this->expectException(ValidationException::class);

        $service->post('DEP-005', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '21.00', 'currency' => 'USD'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '21.00', 'currency' => 'USD'],
        ], idempotencyKey: 'same-key');
    }

    public function test_it_rejects_reuse_of_reference_with_different_payload(): void
    {
        $cash = FinancialAccount::query()->create([
            'account_code' => 'cash-ref-collision',
            'account_type' => 'asset',
            'currency' => 'USD',
        ]);
        $investor = FinancialAccount::query()->create([
            'account_code' => 'investor-ref-collision',
            'account_type' => 'liability',
            'currency' => 'USD',
        ]);
        $service = app(LedgerPostingService::class);

        $service->post('DEP-006', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '30.00', 'currency' => 'USD'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '30.00', 'currency' => 'USD'],
        ]);

        $this->expectException(ValidationException::class);

        $service->post('DEP-006', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '31.00', 'currency' => 'USD'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '31.00', 'currency' => 'USD'],
        ]);
    }

    public function test_it_preserves_eth_precision_without_rounding(): void
    {
        $cash = FinancialAccount::query()->create([
            'account_code' => 'eth-clearing',
            'account_type' => 'asset',
            'currency' => 'ETH',
        ]);
        $investor = FinancialAccount::query()->create([
            'account_code' => 'investor-eth',
            'account_type' => 'liability',
            'currency' => 'ETH',
        ]);

        $transaction = app(LedgerPostingService::class)->post('ETH-001', 'deposit', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '0.123456789012345678', 'currency' => 'ETH'],
            ['account_id' => $investor->id, 'direction' => 'credit', 'amount' => '0.123456789012345678', 'currency' => 'ETH'],
        ]);

        $this->assertSame('0.123456789012345678', $transaction->entries->first()->amount);
    }
}