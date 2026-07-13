<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * For the questions a form cannot ask.
 *
 * Most conditions are written in the panel now — "has at least one client" is a
 * row, not a deploy. This is for the rest: an active subscription over at Stripe,
 * a score from a service, anything that needs a line of real code.
 *
 * Whatever it generates is *found* on its own. There is nothing to register.
 */
class MakeConditionCommand extends GeneratorCommand
{
    protected $name = 'make:onboarding-condition';

    protected $description = 'Create an onboarding condition class (found automatically — nothing to register)';

    protected $type = 'Condition';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/condition.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return (string) config('filament-onboarding.discovery.namespace', $rootNamespace . '\\Onboarding\\Conditions');
    }

    protected function buildClass($name): string
    {
        $key = \Wallacemartinss\FilamentOnboarding\Conditions\ConditionDiscovery::key($name);

        return str_replace(
            ['{{ key }}', '{{ label }}'],
            [$key, Str::headline($key)],
            parent::buildClass($name),
        );
    }

    public function handle(): bool
    {
        if (parent::handle() === false) {
            return false;
        }

        $key = \Wallacemartinss\FilamentOnboarding\Conditions\ConditionDiscovery::key(
            $this->qualifyClass($this->getNameInput()),
        );

        $this->components->info("It answers to the key [{$key}], and it is already in the panel's dropdown — there is nothing to register.");

        return true;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the condition (HasActiveSubscription)'],
        ];
    }
}
