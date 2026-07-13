<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Support\Facades\{DB, Schema};
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * The hardening migration, run against the mess it was written for.
 *
 * 2.0.0 could keep two progress rows for one step: the unique index contained
 * the nullable scope columns, a missing scope was stored as NULL, and a NULL is
 * never equal to another NULL — so `firstOrCreate`, racing against itself,
 * inserted twice and the database had no constraint to raise.
 *
 * The migration spells the missing scope as '' so the index finally bites. But
 * that same spelling makes the leftover duplicates *equal in the eyes of the
 * index* — so unless they are merged first, the UPDATE dies on the very
 * constraint it is restoring, on precisely the installations that need it:
 * the ones upgrading from 2.0.0.
 */
class MigrationTest extends TestCase
{
    public function test_hardening_the_scope_merges_the_duplicates_it_finds(): void
    {
        $this->recreateTheUnhardenedSchema();

        DB::table('onboarding_flows')->insert([
            'id'    => 'flow-1',
            'key'   => 'journey',
            'title' => json_encode(['en' => 'Get started']),
        ]);

        DB::table('onboarding_steps')->insert([
            'id'      => 'step-1',
            'flow_id' => 'flow-1',
            'key'     => 'first',
            'title'   => json_encode(['en' => 'First']),
        ]);

        // Three rows, one progress: two the 2.0.0 race left behind with NULL
        // scopes, and one already spelled with '' by 2.1.0 code running against
        // the not-yet-hardened schema. Each knows a piece the others do not.
        DB::table('onboarding_step_progress')->insert([
            'id'           => 'dup-a', 'flow_id' => 'flow-1', 'step_id' => 'step-1',
            'subject_type' => 'user', 'subject_id' => '1',
            'scope_type'   => null, 'scope_id' => null,
            'seen_at'      => '2026-01-01 00:00:00',
            'meta'         => json_encode(['tour_index' => 3]),
        ]);

        DB::table('onboarding_step_progress')->insert([
            'id'           => 'dup-b', 'flow_id' => 'flow-1', 'step_id' => 'step-1',
            'subject_type' => 'user', 'subject_id' => '1',
            'scope_type'   => null, 'scope_id' => null,
            'completed_at' => '2026-01-02 00:00:00',
            'meta'         => json_encode(['completed_by' => 'tour']),
        ]);

        DB::table('onboarding_step_progress')->insert([
            'id'           => 'dup-c', 'flow_id' => 'flow-1', 'step_id' => 'step-1',
            'subject_type' => 'user', 'subject_id' => '1',
            'scope_type'   => '', 'scope_id' => '',
        ]);

        DB::table('onboarding_flow_progress')->insert([
            'id'           => 'flow-dup-a', 'flow_id' => 'flow-1',
            'subject_type' => 'user', 'subject_id' => '1',
            'scope_type'   => null, 'scope_id' => null,
            'started_at'   => '2026-01-01 00:00:00',
        ]);

        DB::table('onboarding_flow_progress')->insert([
            'id'           => 'flow-dup-b', 'flow_id' => 'flow-1',
            'subject_type' => 'user', 'subject_id' => '1',
            'scope_type'   => null, 'scope_id' => null,
            'completed_at' => '2026-01-03 00:00:00',
        ]);

        // A different subject's row must come through untouched.
        DB::table('onboarding_step_progress')->insert([
            'id'           => 'hers', 'flow_id' => 'flow-1', 'step_id' => 'step-1',
            'subject_type' => 'user', 'subject_id' => '2',
            'scope_type'   => null, 'scope_id' => null,
            'completed_at' => '2026-01-05 00:00:00',
        ]);

        $this->migrate('harden_onboarding_progress_scope');

        // One row per progress — and the one that stays knows everything the
        // three knew: the completion, the furthest stop, who finished it.
        $this->assertSame(1, DB::table('onboarding_step_progress')->where('subject_id', '1')->count());

        $survivor = DB::table('onboarding_step_progress')->where('subject_id', '1')->first();

        $this->assertSame('', $survivor->scope_type);
        $this->assertSame('', $survivor->scope_id);
        $this->assertSame('2026-01-02 00:00:00', $survivor->completed_at);
        $this->assertSame('2026-01-01 00:00:00', $survivor->seen_at);

        $meta = json_decode((string) $survivor->meta, true);

        $this->assertSame(3, $meta['tour_index'] ?? null);
        $this->assertSame('tour', $meta['completed_by'] ?? null);

        $this->assertSame(1, DB::table('onboarding_flow_progress')->count());

        $flowSurvivor = DB::table('onboarding_flow_progress')->first();

        $this->assertSame('2026-01-01 00:00:00', $flowSurvivor->started_at);
        $this->assertSame('2026-01-03 00:00:00', $flowSurvivor->completed_at);

        $untouched = DB::table('onboarding_step_progress')->where('subject_id', '2')->first();

        $this->assertSame('2026-01-05 00:00:00', $untouched->completed_at);
        $this->assertSame('', $untouched->scope_type);
    }

    /**
     * The schema exactly as 2.0.0 shipped it: nullable scope columns, and a
     * unique index that a NULL walks straight through. TestCase already ran
     * every migration, so the hardened tables are torn down and rebuilt
     * without the hardening.
     */
    private function recreateTheUnhardenedSchema(): void
    {
        foreach (['onboarding_preferences', 'onboarding_step_progress', 'onboarding_flow_progress', 'onboarding_steps', 'onboarding_flows'] as $table) {
            Schema::dropIfExists($table);
        }

        $this->migrate('create_onboarding_tables');
        $this->migrate('add_media_to_onboarding_steps');
        $this->migrate('add_visibility_to_onboarding');
    }

    private function migrate(string $name): void
    {
        $migration = include __DIR__ . "/../../database/migrations/{$name}.php.stub";

        $migration->up();
    }
}
