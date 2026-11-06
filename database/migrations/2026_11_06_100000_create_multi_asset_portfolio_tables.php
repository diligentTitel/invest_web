<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investment_assets')) {
            Schema::create('investment_assets', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 180)->unique();
                $table->string('name', 180);
                $table->string('symbol', 24)->nullable()->index();
                $table->string('category', 48)->index();
                $table->string('asset_type', 48);
                $table->string('currency', 12)->default('USD');
                $table->string('data_provider', 32)->nullable();
                $table->string('provider_asset_id', 80)->nullable();
                $table->text('description');
                $table->json('details')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('simulated_positions')) {
            Schema::create('simulated_positions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('investment_asset_id')->constrained()->restrictOnDelete();
                $table->string('quantity', 64);
                $table->string('average_cost', 64);
                $table->string('cost_basis', 64);
                $table->string('realized_pnl', 64)->default('0');
                $table->string('manual_price', 64)->nullable();
                $table->timestamp('manual_price_updated_at')->nullable();
                $table->json('manual_metrics')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'investment_asset_id']);
                $table->index(['user_id', 'updated_at']);
            });
        }

        if (! Schema::hasTable('simulated_transactions')) {
            Schema::create('simulated_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('investment_asset_id')->constrained()->restrictOnDelete();
                $table->string('side', 8);
                $table->string('quantity', 64);
                $table->string('unit_price', 64);
                $table->string('fees', 64)->default('0');
                $table->string('realized_pnl', 64)->default('0');
                $table->string('price_source', 24)->default('manual');
                $table->timestamp('occurred_at');
                $table->timestamps();
                $table->index(['user_id', 'occurred_at']);
                $table->index(['user_id', 'investment_asset_id', 'occurred_at']);
            });
        }

        if (! Schema::hasTable('investment_watchlist')) {
            Schema::create('investment_watchlist', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('investment_asset_id')->constrained()->restrictOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'investment_asset_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_watchlist');
        Schema::dropIfExists('simulated_transactions');
        Schema::dropIfExists('simulated_positions');
        Schema::dropIfExists('investment_assets');
    }
};