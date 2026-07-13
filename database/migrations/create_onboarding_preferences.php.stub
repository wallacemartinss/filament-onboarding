<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the subject decided about onboarding itself.
 *
 * Progress says how far somebody got through a journey. This says whether they
 * want to be shown journeys at all — a decision that belongs to the person, not
 * to any one flow, and that has to outlive the session it was made in: "do not
 * show me this again" is not a promise you get to break at the next login.
 *
 * "Show me later" is deliberately *not* here. It is a session flag: it means
 * "not now", and the next time they come back is a new now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table('preferences'), function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // Same lengths as the progress tables: a five-column unique index on
            // varchar(255) blows past InnoDB's 3072-byte key limit.
            $table->string('subject_type', 191);
            $table->string('subject_id', 64);
            $table->string('scope_type', 191)->default('');
            $table->string('scope_id', 64)->default('');

            // Nobody wants to see it again.
            $table->timestamp('hidden_at')->nullable();

            // They have been welcomed once, so the welcome screen is done with.
            $table->timestamp('welcomed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['subject_type', 'subject_id', 'scope_type', 'scope_id'],
                'onboarding_preferences_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table('preferences'));
    }

    private function table(string $key): string
    {
        return config("filament-onboarding.tables.{$key}", "onboarding_{$key}");
    }
};
