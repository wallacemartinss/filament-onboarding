<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * Definitions are cached, and the cache is read on every panel request.
 *
 * What may not be in it is **objects**. Laravel refuses to unserialize classes
 * out of the cache unless the application lists them, and a stock Laravel 13
 * ships `cache.serializable_classes => false` — so that a leaked APP_KEY cannot
 * be turned into a gadget chain through the cache.
 *
 * A Collection of Eloquent models therefore writes perfectly well and reads back
 * as `__PHP_Incomplete_Class`. The first request of the panel's life populates
 * the cache and works; the second one reads it, fails the return type, and 500s
 * — on every page, because the launcher renders in the layout.
 *
 * The rest of the suite runs with the cache off (definitions change constantly
 * in tests), which is exactly why nothing caught it.
 */
class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // A store that actually serializes, and an application that allows no
        // classes back — the stock Laravel 13 posture.
        config()->set('cache.serializable_classes', false);
        config()->set('cache.stores.serialising', ['driver' => 'array', 'serialize' => true]);

        config()->set('filament-onboarding.cache.enabled', true);
        config()->set('filament-onboarding.cache.store', 'serialising');

        Cache::store('serialising')->clear();

        $flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        OnboardingStep::create([
            'flow_id'         => $flow->id,
            'key'             => 'first',
            'type'            => StepType::Task,
            'title'           => ['en' => 'First'],
            'description'     => ['en' => 'The first one'],
            'completion_mode' => CompletionMode::Manual,
            'is_required'     => true,
        ]);
    }

    public function test_the_second_request_reads_the_cache_it_wrote(): void
    {
        // The write. This one always worked — it returns the value it just
        // computed, without ever going back through the cache.
        $this->assertCount(1, Onboarding::flows());

        // The read. This is the request that used to die.
        $flows = Onboarding::flows();

        $this->assertCount(1, $flows);

        $flow = $flows->first();

        $this->assertInstanceOf(OnboardingFlow::class, $flow);
        $this->assertSame('journey', $flow->key);
        $this->assertTrue($flow->is_active);
        $this->assertSame('Get started', $flow->translate('title'));

        // Rebuilt, not merely present: the relation, the casts and the
        // translatable columns all have to survive the round trip.
        $this->assertCount(1, $flow->steps);

        $step = $flow->steps->first();

        $this->assertInstanceOf(OnboardingStep::class, $step);
        $this->assertSame('first', $step->key);
        $this->assertSame(StepType::Task, $step->type);
        $this->assertSame(CompletionMode::Manual, $step->completion_mode);
        $this->assertTrue($step->is_required);
        $this->assertSame('The first one', $step->translate('description'));
    }

    public function test_nothing_but_arrays_goes_into_the_cache(): void
    {
        Onboarding::flows();

        $cached = Cache::store('serialising')->get('filament-onboarding.flows');

        $this->assertIsArray($cached);

        // The guard, stated the way the failure actually happens: an object in
        // here is an object the application may refuse to give back.
        $this->assertStringNotContainsString(
            'O:',
            serialize($cached),
            'The cached definitions contain an object. Laravel will not unserialize it back when cache.serializable_classes is false, and the panel 500s on the next request.',
        );
    }

    public function test_a_cache_holding_something_else_is_a_miss_rather_than_a_crash(): void
    {
        // An object written by an older release of this package, or junk under a
        // shared prefix. Either way it must not be trusted, and must not throw.
        Cache::store('serialising')->put('filament-onboarding.flows', new Subject(['name' => 'not a flow']), 60);

        $flows = Onboarding::flows();

        $this->assertCount(1, $flows);
        $this->assertSame('journey', $flows->first()->key);

        // And the bad value is gone: the miss overwrote it with the real thing.
        $this->assertIsArray(Cache::store('serialising')->get('filament-onboarding.flows'));
    }

    public function test_writing_a_flow_flushes_what_the_panel_reads(): void
    {
        $this->assertCount(1, Onboarding::flows());

        OnboardingFlow::create([
            'key'       => 'second',
            'title'     => ['en' => 'Another'],
            'is_active' => true,
        ]);

        // Definitions are cached until one is written — the model's saved() hook
        // is what keeps the panel from serving yesterday's journey.
        $this->assertCount(2, Onboarding::flows());
    }
}
