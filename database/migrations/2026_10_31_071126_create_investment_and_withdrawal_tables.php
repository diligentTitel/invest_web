<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('investment_opportunity_id')->constrained()->restrictOnDelete();
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
    $table->string('principal_amount', 64);
} else {
    $table->decimal('principal_amount', 36, 18);
}
            $table->string('currency', 12);
            $table->string('status', 32)->default('pending')->index();
            $table->string('agreement_version', 60)->nullable();
            $table->char('agreement_sha256', 64)->nullable();
            $table->uuid('ledger_transaction_id')->nullable()->unique();
            $table->foreign('ledger_transaction_id')->references('id')->on('financial_transactions')->restrictOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('matures_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('investment_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->restrictOnDelete();
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
    $table->string('amount', 64);
} else {
    $table->decimal('amount', 36, 18);
}
            $table->string('currency', 12);
            $table->string('status', 24)->default('pending')->index();
            $table->string('source_reference', 180)->nullable();
            $table->uuid('ledger_transaction_id')->nullable()->unique();
            $table->foreign('ledger_transaction_id')->references('id')->on('financial_transactions')->restrictOnDelete();
            $table->timestamp('distribution_date')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('financial_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('network_id')->nullable()->constrained('blockchain_networks')->restrictOnDelete();
            $table->string('reference', 100)->unique();
            $table->string('idempotency_key', 160)->unique();
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
    $table->string('amount', 64);
} else {
    $table->decimal('amount', 36, 18);
}
            $table->string('currency', 12);
            $table->text('destination_encrypted');
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('payment_reference', 180)->nullable();
            $table->uuid('ledger_transaction_id')->nullable()->unique();
            $table->foreign('ledger_transaction_id')->references('id')->on('financial_transactions')->restrictOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('investment_distributions');
        Schema::dropIfExists('investments');
    }
};