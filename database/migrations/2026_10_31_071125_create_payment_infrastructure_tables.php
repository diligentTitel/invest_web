<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('supported_cryptocurrencies')) {
            Schema::create('supported_cryptocurrencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 12)->unique();
                $table->string('name', 80);
                $table->unsignedTinyInteger('precision')->default(8);
                $table->boolean('is_enabled')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('blockchain_networks')) {
            Schema::create('blockchain_networks', function (Blueprint $table) {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('name', 80);
                $table->string('chain_identifier', 100)->nullable();
                $table->string('address_format', 100)->nullable();
                $table->unsignedSmallInteger('required_confirmations')->default(1);
                $table->boolean('is_enabled')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crypto_wallets')) {
            Schema::create('crypto_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cryptocurrency_id')->constrained('supported_cryptocurrencies')->restrictOnDelete();
                $table->foreignId('network_id')->constrained('blockchain_networks')->restrictOnDelete();
                $table->text('receiving_address_encrypted');
                $table->char('address_fingerprint', 64);
                $table->string('status', 24)->default('disabled')->index();
                $table->decimal('minimum_deposit', 36, 18)->nullable();
                $table->decimal('maximum_deposit', 36, 18)->nullable();
                $table->unsignedSmallInteger('required_confirmations')->default(1);
                $table->timestamp('effective_from')->nullable();
                $table->timestamp('effective_until')->nullable();
                $table->timestamps();
                $table->unique(['cryptocurrency_id', 'network_id', 'address_fingerprint'], 'crypto_wallet_unique_address');
            });
        }

        if (! Schema::hasTable('payment_providers')) {
            Schema::create('payment_providers', function (Blueprint $table) {
                $table->id();
                $table->string('provider_key', 80)->unique();
                $table->string('display_name', 120);
                $table->string('status', 24)->default('disabled')->index();
                $table->json('public_configuration')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('deposit_requests')) {
            Schema::create('deposit_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('cryptocurrency_id')->constrained('supported_cryptocurrencies')->restrictOnDelete();
                $table->foreignId('network_id')->constrained('blockchain_networks')->restrictOnDelete();
                $table->foreignId('crypto_wallet_id')->nullable()->constrained()->restrictOnDelete();
                $table->foreignId('payment_provider_id')->nullable()->constrained()->restrictOnDelete();
                $table->string('reference', 100)->unique();
                $table->string('idempotency_key', 160)->unique();
                $table->string('method', 24);
                if (Schema::getConnection()->getDriverName() === 'sqlite') {
                    $table->string('requested_amount', 64);
                } else {
                    $table->decimal('requested_amount', 36, 18);
                }
                $table->decimal('expected_crypto_amount', 36, 18)->nullable();
                $table->decimal('received_amount', 36, 18)->nullable();
                $table->string('currency_code', 12);
                $table->string('status', 32)->default('created')->index();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('payment_detected_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_reason')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status', 'created_at']);
            });
        }

        if (! Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('deposit_request_id')->nullable()->constrained()->restrictOnDelete();
                $table->foreignId('payment_provider_id')->nullable()->constrained()->restrictOnDelete();
                $table->foreignId('network_id')->constrained('blockchain_networks')->restrictOnDelete();
                $table->string('provider_payment_id', 180)->nullable();
                $table->string('transaction_hash', 200)->nullable();
                $table->string('event_id', 200)->nullable();
                $table->char('payload_sha256', 64)->nullable();
                $table->string('currency_code', 12);
                $table->decimal('observed_amount', 36, 18)->nullable();
                $table->text('destination_address_encrypted')->nullable();
                $table->unsignedInteger('confirmations')->default(0);
                $table->string('verification_status', 32)->default('unverified')->index();
                $table->string('verification_source', 80)->nullable();
                $table->timestamp('observed_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();
                $table->unique(['network_id', 'transaction_hash'], 'payment_network_tx_unique');
                $table->index(['deposit_request_id', 'verification_status'], 'payment_transactions_request_status_index');
                $table->unique(['payment_provider_id', 'event_id'], 'payment_provider_event_unique');
            });
        }

        $this->ensureIndex(
            'payment_transactions',
            'payment_transactions_verification_status_index',
            ['verification_status'],
        );
        $this->ensureIndex(
            'payment_transactions',
            'payment_transactions_request_status_index',
            ['deposit_request_id', 'verification_status'],
        );
        $this->ensureUniqueIndex(
            'payment_transactions',
            'payment_provider_event_unique',
            ['payment_provider_id', 'event_id'],
        );
    }


    private function ensureIndex(string $tableName, string $indexName, array $columns): void
    {
        $exists = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index): bool => $index['name'] === $indexName);

        if (! $exists) {
            Schema::table($tableName, fn (Blueprint $table) => $table->index($columns, $indexName));
        }
    }

    private function ensureUniqueIndex(string $tableName, string $indexName, array $columns): void
    {
        $exists = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index): bool => $index['name'] === $indexName);

        if (! $exists) {
            Schema::table($tableName, fn (Blueprint $table) => $table->unique($columns, $indexName));
        }
    }

    public function down(): void
    {
        // This migration may adopt payment tables that existed before its migration record.
        // Preserve financial records during rollback; table cleanup requires an explicit operation.
    }
};