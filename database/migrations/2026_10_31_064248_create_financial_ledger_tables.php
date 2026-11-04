<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('account_code', 100)->unique();
            $table->string('account_type', 32)->index();
            $table->string('currency', 12);
            $table->string('status', 24)->default('active');
            $table->timestamps();
            $table->index(['user_id', 'currency', 'status']);
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference', 100)->unique();
            $table->string('transaction_type', 60)->index();
            $table->string('idempotency_key', 160)->nullable()->unique();
            $table->string('status', 24)->default('pending')->index();
            $table->string('description', 500)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('financial_transaction_id')->constrained()->restrictOnDelete();
            $table->foreignId('financial_account_id')->constrained()->restrictOnDelete();
            $table->string('direction', 8);
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
    $table->string('amount', 64);
} else {
    $table->decimal('amount', 36, 18);
}
            $table->string('currency', 12);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['financial_account_id', 'currency', 'created_at']);
            $table->index(['financial_transaction_id', 'currency', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('financial_accounts');
    }
};