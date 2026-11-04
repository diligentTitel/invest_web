<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('slug', 80)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id']);
            });
        }

        foreach ([
            'role_id' => fn (Blueprint $table) => $table->foreignId('role_id')->nullable(),
            'status' => fn (Blueprint $table) => $table->string('status', 24)->default('active'),
            'is_demo' => fn (Blueprint $table) => $table->boolean('is_demo')->default(false),
            'last_login_at' => fn (Blueprint $table) => $table->timestamp('last_login_at')->nullable(),
        ] as $column => $definition) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', $definition);
            }
        }

        if (! Schema::hasTable('investor_profiles')) {
            Schema::create('investor_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
                $table->string('legal_name')->nullable();
                $table->char('country_code', 2)->nullable()->index();
                $table->char('preferred_currency', 3)->default('USD');
                $table->text('phone_encrypted')->nullable();
                $table->text('address_encrypted')->nullable();
                $table->string('verification_status', 32)->default('not_started')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kyc_submissions')) {
            Schema::create('kyc_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('provider', 80)->nullable();
                $table->string('provider_reference', 160)->nullable()->unique();
                $table->string('status', 32)->default('pending')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_reason')->nullable();
                $table->text('metadata')->nullable();
                $table->text('private_document_keys')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 120)->index();
                $table->nullableMorphs('subject');
                $table->uuid('request_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('properties')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['created_at', 'action']);
            });
        }

        $this->ensureIndex('roles', 'roles_slug_unique', ['slug'], true);
        $this->ensureIndex('permissions', 'permissions_name_unique', ['name'], true);
        $this->ensureIndex('investor_profiles', 'investor_profiles_user_id_unique', ['user_id'], true);
        $this->ensureIndex('investor_profiles', 'investor_profiles_country_code_index', ['country_code']);
        $this->ensureIndex('investor_profiles', 'investor_profiles_verification_status_index', ['verification_status']);
        $this->ensureIndex('kyc_submissions', 'kyc_submissions_provider_reference_unique', ['provider_reference'], true);
        $this->ensureIndex('kyc_submissions', 'kyc_submissions_status_index', ['status']);
        $this->ensureIndex('kyc_submissions', 'kyc_submissions_user_id_status_index', ['user_id', 'status']);
        $this->ensureIndex('audit_logs', 'audit_logs_action_index', ['action']);
        $this->ensureIndex('audit_logs', 'audit_logs_subject_type_subject_id_index', ['subject_type', 'subject_id']);
        $this->ensureIndex('audit_logs', 'audit_logs_request_id_index', ['request_id']);
        $this->ensureIndex('audit_logs', 'audit_logs_created_at_action_index', ['created_at', 'action']);

        $this->ensureForeignKey('users', 'role_id', 'roles', 'restrict');
        $this->ensureForeignKey('investor_profiles', 'user_id', 'users', 'restrict');
        $this->ensureForeignKey('kyc_submissions', 'user_id', 'users', 'restrict');
        $this->ensureForeignKey('kyc_submissions', 'reviewed_by', 'users', 'set null');
        $this->ensureForeignKey('audit_logs', 'actor_id', 'users', 'set null');
        $this->ensureForeignKey('permission_role', 'permission_id', 'permissions', 'cascade');
        $this->ensureForeignKey('permission_role', 'role_id', 'roles', 'cascade');
    }

    private function ensureIndex(string $tableName, string $indexName, array $columns, bool $unique = false): void
    {
        $exists = collect(Schema::getIndexes($tableName))->contains(fn (array $index): bool => $index['name'] === $indexName);
        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName, $unique): void {
            $unique ? $table->unique($columns, $indexName) : $table->index($columns, $indexName);
        });
    }

    private function ensureForeignKey(string $tableName, string $column, string $referencedTable, string $onDelete): void
    {
        $exists = collect(Schema::getForeignKeys($tableName))->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === [$column]
                && $foreignKey['foreign_table'] === $referencedTable
        );
        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable, $onDelete): void {
            $foreign = $table->foreign($column)->references('id')->on($referencedTable);
            match ($onDelete) {
                'cascade' => $foreign->cascadeOnDelete(),
                'set null' => $foreign->nullOnDelete(),
                default => $foreign->restrictOnDelete(),
            };
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('kyc_submissions');
        Schema::dropIfExists('investor_profiles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'status', 'is_demo', 'last_login_at']);
        });
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};