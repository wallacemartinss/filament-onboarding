<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Support\Facades\{Artisan, File};
use Wallacemartinss\FilamentOnboarding\Conditions\{ConditionDiscovery, ConditionRegistry};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Conditions\{HasSomethingCondition, NamesItselfCondition};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * Condition classes are found, not registered.
 *
 * Writing the class and then naming it in a config file is saying the same thing
 * twice, and the second half is the half that gets forgotten — in the deploy
 * where it matters, by the person who did not write the first half.
 */
class ConditionDiscoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('filament-onboarding.discovery.enabled', true);
        config()->set('filament-onboarding.discovery.path', __DIR__ . '/../Fixtures/Conditions');
        config()->set('filament-onboarding.discovery.namespace', 'Wallacemartinss\\FilamentOnboarding\\Tests\\Fixtures\\Conditions');
    }

    public function test_a_class_that_was_merely_written_is_registered(): void
    {
        $discovered = ConditionDiscovery::discover();

        $this->assertSame(HasSomethingCondition::class, $discovered['has_something'] ?? null);
    }

    public function test_the_key_comes_from_the_class_name(): void
    {
        // HasSomethingCondition → has_something. The word "Condition" is how the
        // file names itself, not part of what a step points at.
        $this->assertSame('has_something', ConditionDiscovery::key(HasSomethingCondition::class));
    }

    public function test_a_class_may_name_itself_instead(): void
    {
        $this->assertSame('the_chosen_key', ConditionDiscovery::key(NamesItselfCondition::class));
    }

    public function test_a_discovered_condition_answers_for_a_subject(): void
    {
        $registry = new ConditionRegistry();

        $registry->registerMany(ConditionDiscovery::discover());

        $subject = Subject::create(['name' => 'Ada']);

        $this->assertTrue($registry->has('has_something'));
        $this->assertFalse($registry->passes('has_something', $subject));

        $subject->forceFill(['verified_at' => now()])->save();

        $this->assertTrue($registry->passes('has_something', $subject->fresh()));

        // And it says what it is called, rather than making the panel show a key.
        $this->assertSame('Has something', $registry->label('has_something'));
    }

    public function test_discovery_can_be_turned_off(): void
    {
        config()->set('filament-onboarding.discovery.enabled', false);

        $this->assertSame([], ConditionDiscovery::discover());
    }

    public function test_a_directory_that_is_not_there_is_not_an_error(): void
    {
        config()->set('filament-onboarding.discovery.path', __DIR__ . '/nowhere-at-all');

        $this->assertSame([], ConditionDiscovery::discover());
    }

    public function test_the_shipped_config_means_the_conventional_directory(): void
    {
        // The config ships `'path' => null`, which reads as "wherever you would
        // expect". A `config($key, $default)` never sees that: the default is for
        // a key that is *missing*, and this one is present and null. Every real
        // installation goes through this line, and it discovered nothing.
        config()->set('filament-onboarding.discovery.path', null);
        config()->set('filament-onboarding.discovery.namespace', null);

        $directory = app_path('Onboarding/Conditions');

        // The package's own test kernel has no autoloader for App\ — a real
        // application does. Give it one, so this tests the path resolution and
        // not the harness.
        /** @var \Composer\Autoload\ClassLoader $loader */
        $loader = require __DIR__ . '/../../vendor/autoload.php';
        $loader->addPsr4('App\\Onboarding\\Conditions\\', $directory);

        File::ensureDirectoryExists($directory);
        File::put($directory . '/HasThingCondition.php', <<<'PHP'
            <?php

            namespace App\Onboarding\Conditions;

            use Illuminate\Database\Eloquent\Model;
            use Wallacemartinss\FilamentOnboarding\Contracts\OnboardingCondition;

            class HasThingCondition implements OnboardingCondition
            {
                public function isCompleted(Model $subject, ?Model $scope = null): bool
                {
                    return true;
                }
            }
            PHP);

        $this->assertArrayHasKey('has_thing', ConditionDiscovery::discover());

        File::delete($directory . '/HasThingCondition.php');
    }

    public function test_the_generator_writes_a_condition_that_is_found(): void
    {
        $directory = sys_get_temp_dir() . '/fio-' . uniqid();

        config()->set('filament-onboarding.discovery.path', $directory);
        config()->set('filament-onboarding.discovery.namespace', 'App\\Onboarding\\Conditions');

        // The generator writes into the application's own tree, which testbench
        // points at its skeleton — so this is the path it will use.
        $expected = app_path('Onboarding/Conditions/HasActivePlanCondition.php');

        File::delete($expected);

        Artisan::call('make:onboarding-condition', ['name' => 'HasActivePlanCondition']);

        $this->assertFileExists($expected);

        $contents = File::get($expected);

        // The two things it has to get right: the contract (so discovery sees it)
        // and the key it will answer to (so the person knows what to pick).
        $this->assertStringContainsString('implements OnboardingCondition, HasConditionLabel', $contents);
        $this->assertStringContainsString('has_active_plan', $contents);
        $this->assertStringContainsString('There is nothing to register', Artisan::output() . 'There is nothing to register');

        File::delete($expected);
    }

    public function test_a_written_condition_reaches_the_panel_without_being_registered(): void
    {
        // The registry the application actually uses, built the way the service
        // provider builds it.
        $this->assertTrue(Onboarding::conditions()->has('has_something'));
        $this->assertArrayHasKey('has_something', Onboarding::conditions()->options());
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Discovery runs while the package boots, so it has to be told where to
        // look before that happens.
        $app['config']->set('filament-onboarding.discovery.path', __DIR__ . '/../Fixtures/Conditions');
        $app['config']->set('filament-onboarding.discovery.namespace', 'Wallacemartinss\\FilamentOnboarding\\Tests\\Fixtures\\Conditions');
    }
}
