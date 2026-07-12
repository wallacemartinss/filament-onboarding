<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portable across PostgreSQL and MySQL/MariaDB.
 *
 * The morph columns carry explicit lengths on purpose: the progress tables are
 * unique on (step, subject, scope), and with MySQL's default utf8mb4 a
 * five-column index over 255-char strings blows past InnoDB's 3072-byte key
 * limit. At 191/64 the key fits (~2.2 KB) while still holding any class name
 * and any UUID or integer id. PostgreSQL is indifferent either way.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table('flows'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('panel_id')->nullable()->index();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_dismissible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create($this->table('steps'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('flow_id')->constrained($this->table('flows'))->cascadeOnDelete();
            $table->string('key');
            $table->string('type')->default('task');
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->json('cta_label')->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->string('cta_route')->nullable();
            $table->string('completion_mode')->default('manual');
            $table->string('condition_key')->nullable();
            $table->string('visit_url', 2048)->nullable();
            $table->json('tour_steps')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['flow_id', 'key']);
            $table->index(['flow_id', 'is_active', 'sort_order']);
        });

        Schema::create($this->table('flow_progress'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('flow_id')->constrained($this->table('flows'))->cascadeOnDelete();
            $table->string('subject_type', 191);
            $table->string('subject_id', 64);
            $table->string('scope_type', 191)->nullable();
            $table->string('scope_id', 64)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'onboarding_flow_progress_subject_index');
            $table->unique(
                ['flow_id', 'subject_type', 'subject_id', 'scope_type', 'scope_id'],
                'onboarding_flow_progress_unique'
            );
        });

        Schema::create($this->table('step_progress'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('flow_id')->constrained($this->table('flows'))->cascadeOnDelete();
            $table->foreignUuid('step_id')->constrained($this->table('steps'))->cascadeOnDelete();
            $table->string('subject_type', 191);
            $table->string('subject_id', 64);
            $table->string('scope_type', 191)->nullable();
            $table->string('scope_id', 64)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'onboarding_step_progress_subject_index');
            $table->index(['flow_id', 'subject_type', 'subject_id'], 'onboarding_step_progress_flow_subject_index');
            $table->unique(
                ['step_id', 'subject_type', 'subject_id', 'scope_type', 'scope_id'],
                'onboarding_step_progress_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table('step_progress'));
        Schema::dropIfExists($this->table('flow_progress'));
        Schema::dropIfExists($this->table('steps'));
        Schema::dropIfExists($this->table('flows'));
    }

    private function table(string $key): string
    {
        return config("filament-onboarding.tables.{$key}", "onboarding_{$key}");
    }
};
