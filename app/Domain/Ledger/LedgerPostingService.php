<?php

namespace App\Domain\Ledger;

use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LedgerPostingService
{
    /**
     * Post a balanced, append-only ledger transaction.
     * Each entry requires account_id, direction (debit|credit), decimal amount, and currency.
     */
    public function post(
        string $reference,
        string $type,
        array $entries,
        ?int $actorId = null,
        ?string $idempotencyKey = null,
        ?string $description = null,
        array $metadata = [],
    ): FinancialTransaction {
        $normalized = $this->normalizeAndValidate($entries);
        $fingerprint = hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR));

        return DB::transaction(function () use (
            $reference, $type, $normalized, $actorId, $idempotencyKey, $description, $metadata, $fingerprint
        ): FinancialTransaction {
            $existing = $this->findExistingTransaction($reference, $idempotencyKey, $fingerprint);

            if ($existing !== null) {
                return $existing;
            }

            $accountIds = collect($normalized)->pluck('financial_account_id')->unique()->sort()->values();
            $accounts = FinancialAccount::query()
                ->whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($accounts->count() !== $accountIds->count()) {
                throw ValidationException::withMessages(['entries' => 'One or more financial accounts do not exist.']);
            }

            foreach ($normalized as $entry) {
                $account = $accounts->get($entry['financial_account_id']);
                if ($account->status !== 'active' || $account->currency !== $entry['currency']) {
                    throw ValidationException::withMessages([
                        'entries' => 'Ledger entries require active accounts with matching currencies.',
                    ]);
                }
            }

            $transaction = FinancialTransaction::query()->create([
                'id' => (string) Str::uuid(),
                'reference' => $reference,
                'transaction_type' => $type,
                'idempotency_key' => $idempotencyKey,
                'status' => 'pending',
                'description' => $description,
                'actor_id' => $actorId,
                'metadata' => array_merge($metadata, ['request_fingerprint' => $fingerprint]),
            ]);

            foreach ($normalized as $entry) {
                $transaction->entries()->create($entry);
            }

            $transaction->forceFill(['status' => 'posted', 'posted_at' => now()])->save();

            return $transaction->load('entries');
        }, attempts: 3);
    }

    private function findExistingTransaction(string $reference, ?string $idempotencyKey, string $fingerprint): ?FinancialTransaction
    {
        if ($idempotencyKey !== null) {
            $existing = FinancialTransaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                if (($existing->metadata['request_fingerprint'] ?? null) !== $fingerprint) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This key was already used for a different ledger request.',
                    ]);
                }

                return $existing;
            }
        }

        $existing = FinancialTransaction::query()
            ->where('reference', $reference)
            ->first();

        if ($existing) {
            if (($existing->metadata['request_fingerprint'] ?? null) !== $fingerprint) {
                throw ValidationException::withMessages([
                    'reference' => 'This ledger reference was already used for a different transaction.',
                ]);
            }

            return $existing;
        }

        return null;
    }

    private function normalizeAndValidate(array $entries): array
    {
        if (count($entries) < 2) {
            throw ValidationException::withMessages(['entries' => 'A ledger transaction needs at least two entries.']);
        }

        $totals = [];
        $normalized = [];

        foreach ($entries as $entry) {
            $direction = strtolower((string) ($entry['direction'] ?? ''));
            $currency = strtoupper((string) ($entry['currency'] ?? ''));
            $accountId = filter_var($entry['account_id'] ?? null, FILTER_VALIDATE_INT);
            $precision = config('platform.currency_precision.'.$currency);

            if (! in_array($direction, ['debit', 'credit'], true)
                || ! preg_match('/^[A-Z0-9]{2,12}$/', $currency)
                || ! is_int($precision)
                || $accountId === false
                || $accountId < 1) {
                throw ValidationException::withMessages(['entries' => 'A ledger entry is malformed or uses an unsupported currency.']);
            }

            try {
                $amount = BigDecimal::of((string) ($entry['amount'] ?? ''));
                if (! $amount->isGreaterThan(BigDecimal::zero())) {
                    throw ValidationException::withMessages(['entries' => 'Ledger amounts must be greater than zero.']);
                }
                $amount = $amount->toScale($precision);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'entries' => 'Ledger amount is invalid or exceeds the configured precision for this currency.',
                ]);
            }

            $totals[$currency] ??= ['debit' => BigDecimal::zero(), 'credit' => BigDecimal::zero()];
            $totals[$currency][$direction] = $totals[$currency][$direction]->plus($amount);

            $normalized[] = [
                'financial_account_id' => $accountId,
                'direction' => $direction,
                'amount' => (string) $amount,
                'currency' => $currency,
            ];
        }

        foreach ($totals as $currency => $sides) {
            if ($sides['debit']->compareTo($sides['credit']) !== 0) {
                throw ValidationException::withMessages([
                    'entries' => 'Ledger debits and credits must balance for '.$currency.'.',
                ]);
            }
        }

        usort($normalized, static fn (array $left, array $right): int =>
            [$left['financial_account_id'], $left['direction'], $left['currency'], $left['amount']]
            <=> [$right['financial_account_id'], $right['direction'], $right['currency'], $right['amount']]
        );

        return $normalized;
    }
}