<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Conditions;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Wallacemartinss\FilamentOnboarding\Contracts\{HasConditionLabel, OnboardingCondition};

/**
 * Named checks the application exposes so a step authored in the panel can ask
 * a real question about the subject — "has this user created a server yet?".
 */
class ConditionRegistry
{
    /** @var array<string, Closure|class-string> */
    protected array $conditions = [];

    /** @var array<string, string> */
    protected array $labels = [];

    /**
     * @param  Closure|class-string  $condition  An invokable/OnboardingCondition class, or a closure.
     */
    public function register(string $key, Closure|string $condition, ?string $label = null): static
    {
        $this->conditions[$key] = $condition;

        if ($label !== null) {
            $this->labels[$key] = $label;
        }

        return $this;
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
        return array_keys($this->conditions);
    }
}
