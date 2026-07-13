<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conditions written in the panel.
 *
 * A condition is the question onboarding asks about somebody — "have they added
 * a client yet?" — and until now every one of them was code: a closure in a
 * service provider, or a class named in the config. Which meant the package kept
 * its promise ("journeys are authored in the panel, not in code") right up until
 * the most valuable kind of step, and then quietly asked for a deploy.
 *
 * Most of those questions have the same shape: *does this subject have at least
 * N rows of some model, matching some filters?* — or, simpler still, *is a column
 * on the subject set to something?* Both are entirely expressible in a form, and
 * that is what this table holds.
 *
 * What it deliberately does not hold is SQL. The model is picked from a list the
 * application allows, the column from that table's real columns, and the operator
 * from an enum. Nothing an author types is ever interpolated into a query — it is
 * bound as a value.
 *
 * Conditions that genuinely need code still can be: a class in
 * app/Onboarding/Conditions is discovered and registered on its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table('conditions'), function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // What a step points at. Same shape as a code condition's key, and
            // they share one namespace: an author cannot shadow `has_server`
            // with a table row.
            $table->string('key')->unique();

            $table->json('label');
            $table->json('description')->nullable();

            // aggregate: count rows of another model that belong to the subject.
            // attribute: ask about a column on the subject itself.
            $table->string('type')->default('aggregate');

            $table->string('model')->nullable();
            $table->string('subject_column')->nullable();
            $table->string('scope_column')->nullable();

            $table->json('filters')->nullable();

            // "At least this many." One, almost always.
            $table->unsignedInteger('minimum')->default(1);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table('conditions'));
    }

    private function table(string $key): string
    {
        return config("filament-onboarding.tables.{$key}", "onboarding_{$key}");
    }
};
