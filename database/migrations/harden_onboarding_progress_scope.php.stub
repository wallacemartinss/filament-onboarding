<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

/**
 * The unique index on progress was a no-op for applications without tenants.
 *
 * Progress is unique per (step, subject, scope). The scope columns were nullable,
 * and in SQL a NULL is not equal to another NULL — so a unique index containing
 * one enforces nothing for the rows where it is null, on Postgres and MySQL
 * alike. An application that does not use tenants writes every row with a null
 * scope, which is every row.
 *
 * That index is not decoration: `firstOrCreate` leans on it. Two requests arrive
 * together — the launcher renders on every page, and people keep tabs open —
 * both look, both miss, both insert, and the database, having no constraint to
 * raise, keeps both. From then on the subject has two progress rows for one step
 * and their progress flickers depending on which one is read first.
 *
 * The absence of a scope is now spelled with an empty string, which the database
 * is willing to compare.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->mergeDuplicates($this->table('flow_progress'), 'flow_id', ['started_at', 'completed_at', 'dismissed_at']);
        $this->mergeDuplicates($this->table('step_progress'), 'step_id', ['completed_at', 'skipped_at', 'seen_at']);

        foreach (['flow_progress', 'step_progress'] as $key) {
            $table = $this->table($key);

            DB::table($table)->whereNull('scope_type')->update(['scope_type' => '']);
            DB::table($table)->whereNull('scope_id')->update(['scope_id' => '']);

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('scope_type', 191)->nullable(false)->default('')->change();
                $blueprint->string('scope_id', 64)->nullable(false)->default('')->change();
            });
        }
    }

    /**
     * The duplicates this migration exists to prevent may already be in the
     * table — that is the point of it. Turning NULL into '' below makes those
     * rows equal in the eyes of the unique index, and the UPDATE would die on
     * the very constraint it is trying to restore. So they are merged first:
     * the oldest row stays, and it inherits the furthest the others got — the
     * latest of each timestamp, the union of the metas.
     *
     * NULL and '' land in the same bucket on purpose: a NULL-scoped row from
     * before this migration and an ''-scoped row from after it are the same
     * progress, about to be spelled the same way.
     *
     * @param  array<int, string>  $timestamps
     */
    private function mergeDuplicates(string $table, string $ownerColumn, array $timestamps): void
    {
        $duplicated = DB::table($table)
            ->selectRaw("{$ownerColumn} as owner_id")
            ->selectRaw('subject_type, subject_id')
            ->selectRaw("coalesce(scope_type, '') as scope_type_key")
            ->selectRaw("coalesce(scope_id, '') as scope_id_key")
            ->groupByRaw("{$ownerColumn}, subject_type, subject_id, coalesce(scope_type, ''), coalesce(scope_id, '')")
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($duplicated as $group) {
            $rows = DB::table($table)
                ->where($ownerColumn, $group->owner_id)
                ->where('subject_type', $group->subject_type)
                ->where('subject_id', $group->subject_id)
                ->whereRaw("coalesce(scope_type, '') = ?", [$group->scope_type_key])
                ->whereRaw("coalesce(scope_id, '') = ?", [$group->scope_id_key])
                ->orderBy('id')
                ->get();

            $survivor = $rows->first();
            $merged   = [];

            foreach ($timestamps as $column) {
                $furthest = $rows->pluck($column)->filter()->max();

                if ($furthest !== null && $furthest !== $survivor->{$column}) {
                    $merged[$column] = $furthest;
                }
            }

            $meta = $rows
                ->pluck('meta')
                ->map(fn ($value) => is_string($value) ? json_decode($value, true) : null)
                ->filter(fn ($value) => is_array($value))
                ->reduce(fn (array $carry, array $value): array => array_replace($carry, $value), []);

            if ($meta !== []) {
                $merged['meta'] = json_encode($meta);
            }

            if ($merged !== []) {
                DB::table($table)->where('id', $survivor->id)->update($merged);
            }

            DB::table($table)->whereIn('id', $rows->skip(1)->pluck('id')->all())->delete();
        }
    }

    public function down(): void
    {
        foreach (['flow_progress', 'step_progress'] as $key) {
            $table = $this->table($key);

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('scope_type', 191)->nullable()->default(null)->change();
                $blueprint->string('scope_id', 64)->nullable()->default(null)->change();
            });

            DB::table($table)->where('scope_type', '')->update(['scope_type' => null]);
            DB::table($table)->where('scope_id', '')->update(['scope_id' => null]);
        }
    }

    private function table(string $key): string
    {
        return config("filament-onboarding.tables.{$key}", "onboarding_{$key}");
    }
};
