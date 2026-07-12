<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

/**
 * Wipe a subject's progress through a flow.
 *
 * Handy while building a journey — walk it, reset it, walk it again — and the
 * way to give somebody a fresh start without touching the database by hand.
 *
 * Steps completed by a condition come straight back: they answer to the
 * application, and the application has not changed its mind.
 */
class ResetOnboardingCommand extends Command
{
    protected $signature = 'onboarding:reset
        {flow : The key of the flow to reset}
        {--subject= : The id of the subject (a user id, usually)}
        {--subject-model= : The subject model class, when it is not the default}
        {--scope= : The id of the scope (a tenant id, usually)}
        {--scope-model= : The scope model class}';

    protected $description = 'Reset a subject\'s progress through an onboarding flow';

    public function handle(): int
    {
        $flowKey = (string) $this->argument('flow');

        if (Onboarding::flow($flowKey) === null) {
            $this->components->error("No active flow with the key [{$flowKey}].");

            return self::FAILURE;
        }

        $subject = $this->resolve((string) $this->option('subject-model') ?: config('auth.providers.users.model'), $this->option('subject'));

        if (!$subject instanceof Model) {
            $this->components->error('Could not find the subject. Pass --subject=<id> (and --subject-model when it is not your user model).');

            return self::FAILURE;
        }

        $scope = $this->option('scope')
            ? $this->resolve((string) $this->option('scope-model'), $this->option('scope'))
            : null;

        Onboarding::for($subject, $scope)->reset($flowKey);

        $this->components->info("Reset [{$flowKey}] for {$subject->getMorphClass()} {$subject->getKey()}.");

        if (Onboarding::for($subject, $scope)->flow($flowKey)?->hasConditionSteps()) {
            $this->components->warn('Steps completed by a condition will come back completed — they answer to the application, not to this command.');
        }

        return self::SUCCESS;
    }

    private function resolve(?string $model, mixed $id): ?Model
    {
        if (blank($model) || blank($id) || !class_exists($model)) {
            return null;
        }

        return $model::query()->find($id);
    }
}
