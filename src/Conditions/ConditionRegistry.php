<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Conditions;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Wallacemartinss\FilamentOnboarding\Contracts\{HasConditionLabel, OnboardingCondition};

/**
 * Named checks a step authored in the panel can ask about the subject — "has
 * this user created a server yet?".
 *
 * They come from three places, and by the time anything asks, all three are here:
 *
 *   1. Code the application registered — a closure, or a class named in the config.
 *   2. Code the application merely *wrote* — a class in app/Onboarding/Conditions,
 *      found on its own, because writing it and then registering it is saying the
 *      same thing twice.
 *   3. The panel — a condition an author built in a form, which is a row rather
 *      than a deploy.
 *
 * Code wins a name clash. An author cannot shadow `has_server` with a table row
 * and quietly change what a step means.
 */
class ConditionRegistry
{
    /** @var array<string, Closure|class-string> */
    protected array $conditions = [];

    /** @var array<string, string> */
    protected array $labels = [];

    /**
     * Which of them came from code. A condition written in the panel may not take
     * a name that code already answers to, and the panel has to know which those
     * are in order to say so.
     *
     * @var array<string, true>
     */
    protected array $fromCode = [];

    /**
     * Reads the conditions written in the panel. Deferred, because the registry
     * is built while the application boots and the database is not a thing to
     * touch there — and because a request that never asks about a condition
     * should never pay for one.
     *
     * @var (Closure(): array<string, array{condition: Closure, label: string}>)|null
     */
    protected ?Closure $recordLoader = null;

    protected bool $loadedRecords = false;

    /**
     * @param  Closure|class-string  $condition  An invokable/OnboardingCondition class, or a closure.
     */
    public function register(string $key, Closure|string $condition, ?string $label = null): static
    {
        $this->conditions[$key] = $condition;
        $this->fromCode[$key]   = true;

        if ($label !== null) {
            $this->labels[$key] = $label;
        }

        return $this;
    }

    /**
     * @param  Closure(): array<string, array{condition: Closure, label: string}>  $loader
     */
    public function loadRecordsUsing(Closure $loader): static
    {
        $this->recordLoader  = $loader;
        $this->loadedRecords = false;

        return $this;
    }

    /**
     * Drop what was read from the panel, so the next question reads it again.
     *
     * Called when the cache is flushed, which is what happens the moment somebody
     * writes a condition. Without it, the registry would go on answering from the
     * copy it loaded at the start of the request — and an author who has just
     * switched a condition off would watch it keep passing.
     *
     * Code conditions are untouched: they were not read from anywhere.
     */
    public function forgetRecords(): static
    {
        foreach (array_keys($this->conditions) as $key) {
            if (!isset($this->fromCode[$key])) {
                unset($this->conditions[$key], $this->labels[$key]);
            }
        }

        $this->loadedRecords = false;

        return $this;
    }

    /**
     * Bring in the conditions written in the panel, once per request.
     *
     * Nothing in here may throw. It runs behind `has()`, which runs behind every
     * visibility check, which runs on every page of the panel — and the table may
     * genuinely not be there yet (a package updated but not migrated). A source of
     * conditions that cannot be read contributes none, and says so in the log.
     */
    protected function loadRecords(): void
    {
        if ($this->loadedRecords || $this->recordLoader === null) {
            return;
        }

        // Set before the call, not after: a loader that throws must not be retried
        // on every question asked for the rest of the request.
        $this->loadedRecords = true;

        try {
            foreach (($this->recordLoader)() as $key => $recorded) {
                // Code wins.
                if (array_key_exists($key, $this->conditions)) {
                    continue;
                }

                $this->conditions[$key] = $recorded['condition'];
                $this->labels[$key]     = $recorded['label'];
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  array<string, Closure|class-string>  $conditions
     */
    public function registerMany(array $conditions): static
    {
        foreach ($conditions as $key => $condition) {
            $this->register($key, $condition);
        }

        return $this;
    }

    public function has(string $key): bool
    {
        $this->loadRecords();

        return array_key_exists($key, $this->conditions);
    }

    /**
     * Whether a named check holds for this subject.
     *
     * Onboarding is not the product, and it must never be the reason the product
     * is down. This runs on every panel page — the launcher sits in the layout —
     * so a condition that throws would 500 every screen the subject opens, not
     * merely the checklist. A condition can throw for reasons that have nothing
     * to do with onboarding: a relation renamed in a deploy, a database that
     * blinked, an external call that timed out, a class deleted from the code
     * while a flow in the database still names it.
     *
     * So the answer to a broken question is the same as the answer to a question
     * nobody registered: **no**. The step stays pending, the guarded content
     * stays hidden, the failure is logged, and the panel keeps working.
     */
    public function passes(string $key, Model $subject, ?Model $scope = null): bool
    {
        $this->loadRecords();

        $condition = $this->conditions[$key] ?? null;

        if ($condition === null) {
            return false;
        }

        try {
            return $this->evaluate($condition, $subject, $scope);
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * @param  Closure|class-string  $condition
     */
    protected function evaluate(Closure|string $condition, Model $subject, ?Model $scope): bool
    {
        if ($condition instanceof Closure) {
            return (bool) $condition($subject, $scope);
        }

        $instance = app($condition);

        if ($instance instanceof OnboardingCondition) {
            return $instance->isCompleted($subject, $scope);
        }

        if (is_callable($instance)) {
            return (bool) $instance($subject, $scope);
        }

        return false;
    }

    /**
     * Keys offered when authoring a step, labelled for the panel.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $this->loadRecords();

        return collect($this->conditions)
            ->keys()
            ->mapWithKeys(fn (string $key): array => [$key => $this->label($key)])
            ->all();
    }

    /**
     * The label given when registering, or the one the condition names itself
     * with — falling back to the key, which at least says something.
     */
    public function label(string $key): string
    {
        if (isset($this->labels[$key])) {
            return $this->labels[$key];
        }

        $condition = $this->conditions[$key] ?? null;

        if (is_string($condition) && is_a($condition, HasConditionLabel::class, true)) {
            return $condition::label();
        }

        return $key;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        $this->loadRecords();

        return array_keys($this->conditions);
    }

    /**
     * The keys that come from code, which is what a panel-written condition may
     * not take for itself.
     *
     * @return array<int, string>
     */
    public function codeKeys(): array
    {
        return array_keys($this->fromCode);
    }
}
