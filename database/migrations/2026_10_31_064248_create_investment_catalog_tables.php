<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investment_categories')) {
            Schema::create('investment_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('slug', 140)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('investment_opportunities')) {
            Schema::create('investment_opportunities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('investment_categories')->restrictOnDelete();
                $table->string('title', 180);
                $table->string('slug', 200)->unique();
                $table->string('asset_reference', 120)->nullable()->index();
                $table->string('structure_type', 80)->nullable();
                $table->text('description');
                $table->string('currency', 12)->default('USD');
                $table->decimal('minimum_investment', 36, 18)->nullable();
                $table->decimal('maximum_investment', 36, 18)->nullable();
                $table->unsignedSmallInteger('duration_months')->nullable();
                $table->string('risk_classification', 32)->nullable();
                $table->json('documented_return_terms')->nullable();
                $table->string('distribution_schedule', 80)->nullable();
                $table->json('eligible_jurisdictions')->nullable();
                $table->string('legal_review_status', 32)->default('not_reviewed')->index();
                $table->string('availability_status', 32)->default('draft')->index();
                $table->boolean('is_demo')->default(false)->index();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->index(['category_id', 'availability_status']);
            });
        }

        if (! Schema::hasTable('investment_documents')) {
            Schema::create('investment_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investment_opportunity_id')->constrained()->restrictOnDelete();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('document_type', 80);
                $table->string('version', 40)->nullable();
                $table->string('storage_key', 500);
                $table->char('sha256', 64);
                $table->boolean('is_investor_visible')->default(false);
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        $this->ensureIndex('investment_documents', 'inv_docs_offer_type_idx', ['investment_opportunity_id', 'document_type']);
    }

    private function ensureIndex(string $tableName, string $indexName, array $columns): void
    {
        $exists = collect(Schema::getIndexes($tableName))->contains(fn (array $index): bool => $index['name'] === $indexName);
        if (! $exists) {
            Schema::table($tableName, fn (Blueprint $table) => $table->index($columns, $indexName));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_documents');
        Schema::dropIfExists('investment_opportunities');
        Schema::dropIfExists('investment_categories');
    }
};