<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visibility conditions.
 *
 * Not every step is for everybody. A plan that does not include Docker must not
 * be taught Docker: the tour would spotlight a card that is not on the screen,
 * and the checklist would ask for something the account cannot do.
 *
 * A flow or a step with a `visibility_condition` only exists for subjects the
 * condition passes for. Steps hidden this way do not count towards the
 * percentage either — the journey a subject sees is the journey they can walk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->table('flows'), function (Blueprint $table): void {
            $table->string('visibility_condition')->nullable();
        });

        Schema::table($this->table('steps'), function (Blueprint $table): void {
            $table->string('visibility_condition')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table($this->table('flows'), function (Blueprint $table): void {
            $table->dropColumn('visibility_condition');
        });

        Schema::table($this->table('steps'), function (Blueprint $table): void {
            $table->dropColumn('visibility_condition');
        });
    }

    private function table(string $key): string
    {
        return config("filament-onboarding.tables.{$key}", "onboarding_{$key}");
    }
};
