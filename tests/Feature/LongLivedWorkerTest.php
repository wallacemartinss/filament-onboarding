<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\{Cache, DB, Event};
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * The definitions are memoised for the length of one request, so that a panel
 * page does not ask the cache store the same question a dozen times over.
 *
 * On FPM that memo cannot outlive the request that made it — the process ends.
 * On a worker that survives, it can, and "once per request" quietly becomes
 * "once per worker": the manager is the same object on the next request, the
 * write that would have cleared it fired its model events in a *different*
 * process, and this one goes on serving a flow the author switched off.
 *
 * What follows is that scenario, written out in full: this process reads, some
 * other process writes and flushes what they share, and then this one reaches
 * the boundary between two units of work.
 */
class LongLivedWorkerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('filament-onboarding.cache.enabled', true);
        config()->set('filament-onboarding.cache.store', 'array');

        Cache::store('array')->clear();

        OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);
    }

    public function test_the_memo_does_not_outlive_the_job_that_filled_it(): void
    {
        // Request one, in this worker.
        $this->assertCount(1, Onboarding::flows());

        $this->anotherProcessAddsAFlow();

        // Same object, next job. This is the boundary a queue worker has.
        Event::dispatch(new JobProcessing('redis', $this->job()));

        $this->assertCount(
            2,
            Onboarding::flows(),
            'The worker is still serving the definitions it read on an earlier job.',
        );
    }

    public function test_forgetting_the_memo_is_not_flushing_what_the_processes_share(): void
    {
        Onboarding::flows();

        $this->assertIsArray(
            Cache::store('array')->get('filament-onboarding.flows'),
            'The first read should have populated the shared cache.',
        );

        Onboarding::forgetMemoized();

        // Dropping the copy this process holds must leave the copy every
        // process shares alone. Throwing that away on the way into each request
        // would put the whole panel back on the database, once per request, for
        // ever — which is the opposite of what the memo is for.
        $this->assertIsArray(Cache::store('array')->get('filament-onboarding.flows'));
    }

    public function test_writing_a_flow_still_flushes_what_the_processes_share(): void
    {
        Onboarding::flows();

        OnboardingFlow::create([
            'key'       => 'second',
            'title'     => ['en' => 'Another'],
            'is_active' => true,
        ]);

        // The write is the one case where the shared copy *is* wrong for
        // everybody, and it has to go.
        $this->assertNull(Cache::store('array')->get('filament-onboarding.flows'));
        $this->assertCount(2, Onboarding::flows());
    }

    /**
     * A flow written where this process's model events cannot hear it, and the
     * shared cache flushed the way the writing process would have flushed it.
     */
    private function anotherProcessAddsAFlow(): void
    {
        DB::table('onboarding_flows')->insert([
            'id'         => (string) Str::uuid(),
            'key'        => 'second',
            'title'      => json_encode(['en' => 'Another']),
            'is_active'  => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::store('array')->forget('filament-onboarding.flows');
    }

    private function job(): Job
    {
        // The event carries one, and nothing here looks at it.
        return $this->createMock(Job::class);
    }
}
