<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use function Filament\authorize;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingServiceProvider;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingCondition, OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Policies\{OnboardingConditionPolicy, OnboardingFlowPolicy, OnboardingStepPolicy};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;

use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * A panel running ->strictAuthorization() throws for any resource model without
 * a policy — which used to be all three of this package's. The package now
 * ships one per model, permissive on purpose (the access the resources had
 * before policies existed), and yielding to anything the application says.
 */
class StrictAuthorizationTest extends TestCase
{
    /**
     * Every ability Filament may probe on these resources. Strict mode throws
     * for a missing *method* just as it does for a missing policy, so the
     * shipped policies must answer all of them.
     */
    private const ABILITIES_WITHOUT_RECORD = ['viewAny', 'create', 'deleteAny', 'forceDeleteAny', 'restoreAny', 'reorder'];

    private const ABILITIES_WITH_RECORD = ['view', 'update', 'delete', 'forceDelete', 'restore', 'replicate'];

    public function test_the_package_registers_a_policy_for_each_panel_model(): void
    {
        $this->assertInstanceOf(OnboardingFlowPolicy::class, Gate::getPolicyFor(OnboardingFlow::class));
        $this->assertInstanceOf(OnboardingStepPolicy::class, Gate::getPolicyFor(OnboardingStep::class));
        $this->assertInstanceOf(OnboardingConditionPolicy::class, Gate::getPolicyFor(OnboardingCondition::class));
    }

    public function test_strict_authorization_does_not_throw_on_the_panel_models(): void
    {
        Filament::getPanel('test')->strictAuthorization();

        $this->actingAs(Subject::create(['name' => 'Ada']));

        $flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        // The issue's exact shape: entering a strict panel died on viewAny with
        // "no policy was found for [OnboardingFlow]". Now every ability answers.
        foreach (self::ABILITIES_WITHOUT_RECORD as $ability) {
            $this->assertTrue(authorize($ability, OnboardingFlow::class)->allowed(), "viewless ability [{$ability}]");
        }

        foreach (self::ABILITIES_WITH_RECORD as $ability) {
            $this->assertTrue(authorize($ability, $flow)->allowed(), "record ability [{$ability}]");
        }

        // The steps live in a relation manager, which authorizes against the
        // related model; the conditions are a resource of their own.
        $this->assertTrue(authorize('viewAny', OnboardingStep::class)->allowed());
        $this->assertTrue(authorize('viewAny', OnboardingCondition::class)->allowed());
    }

    public function test_a_policy_the_application_registers_wins(): void
    {
        // The application's provider boots after the package's, so its
        // registration lands on top — this is that, compressed into one test.
        Gate::policy(OnboardingFlow::class, DeniesEverythingPolicy::class);

        $subject = Subject::create(['name' => 'Ada']);

        $this->assertTrue(Gate::forUser($subject)->denies('viewAny', OnboardingFlow::class));
    }

    public function test_an_existing_policy_is_not_clobbered_by_the_default(): void
    {
        Gate::policy(OnboardingFlow::class, DeniesEverythingPolicy::class);

        // Boot the package again on top of it: a policy the application can
        // already resolve must be left exactly where it is.
        $provider = $this->app->getProvider(FilamentOnboardingServiceProvider::class);
        $provider->packageBooted();

        $this->assertInstanceOf(DeniesEverythingPolicy::class, Gate::getPolicyFor(OnboardingFlow::class));
    }

    #[DefineEnvironment('usePolicyFromConfig')]
    public function test_a_policy_named_in_config_is_registered_instead(): void
    {
        $this->assertInstanceOf(DeniesEverythingPolicy::class, Gate::getPolicyFor(OnboardingFlow::class));

        // The other models keep the default: naming one policy is not an
        // opinion about the rest.
        $this->assertInstanceOf(OnboardingStepPolicy::class, Gate::getPolicyFor(OnboardingStep::class));
    }

    /**
     * The stock models need no registration at all — the policies live in the
     * package's Policies namespace, which is where Laravel's guesser looks. A
     * swapped model lives in the application's namespace, where nothing of
     * ours is guessable, so that one is registered explicitly.
     */
    #[DefineEnvironment('useSwappedFlowModel')]
    public function test_a_swapped_model_still_gets_the_default_policy(): void
    {
        $this->assertInstanceOf(OnboardingFlowPolicy::class, Gate::getPolicyFor(SwappedFlow::class));
    }

    protected function usePolicyFromConfig($app): void
    {
        $app['config']->set('filament-onboarding.policies.flow', DeniesEverythingPolicy::class);
    }

    protected function useSwappedFlowModel($app): void
    {
        $app['config']->set('filament-onboarding.models.flow', SwappedFlow::class);
    }
}

/**
 * A model swapped in by the application, outside the package namespace.
 */
class SwappedFlow extends OnboardingFlow
{
}

/**
 * The opposite of the shipped default, to make "yours wins" observable.
 */
class DeniesEverythingPolicy
{
    public function viewAny(mixed $user): bool
    {
        return false;
    }
}
