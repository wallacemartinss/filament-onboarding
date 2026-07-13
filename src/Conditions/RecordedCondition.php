<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Conditions;

use Illuminate\Database\Eloquent\{Builder, Model};
use Wallacemartinss\FilamentOnboarding\Enums\{ConditionOperator, ConditionType};
use Wallacemartinss\FilamentOnboarding\Models\OnboardingCondition;
use Wallacemartinss\FilamentOnboarding\Support\AppModels;

/**
 * Answers a condition that was written in the panel.
 *
 * Two questions, and between them they cover what onboarding actually asks:
 *
 *   aggregate — "does this subject have at least N clients that are active?"
 *               A count over another model, scoped to the subject (and to the
 *               tenant, when the model is scoped to one).
 *
 *   attribute — "is this subject's email verified?"
 *               A look at the subject already in memory. No query at all.
 *
 * **Nothing an author typed is ever put into SQL.** The model comes from a list
 * the application allows, the column from that table's real columns, the operator
 * from an enum — and the one thing the author is free to type, the value, is the
 * one thing that is bound rather than interpolated. An author who writes
 * `1); drop table users; --` into the value box asks whether a column equals that
 * string, and gets told no.
 */
final class RecordedCondition
{
    public function __construct(private readonly OnboardingCondition $record)
    {
    }

    public function passes(Model $subject, ?Model $scope = null): bool
    {
        return match ($this->record->type) {
            ConditionType::Attribute => $this->subjectMatches($subject),
            ConditionType::Aggregate => $this->hasEnough($subject, $scope),
        };
    }

    /**
     * A question about the subject itself. The subject is already loaded — this
     * runs on every panel render — so it is answered in PHP rather than by going
     * back to the database for a row we are holding.
     */
    private function subjectMatches(Model $subject): bool
    {
        foreach ($this->filters() as $filter) {
            $operator = $filter['operator'];

            if (!$operator->matches($subject->getAttribute($filter['column']), $filter['value'])) {
                return false;
            }
        }

        // A condition with no filters at all asks nothing, and a question with no
        // question is not something to answer "yes" to.
        return filled($this->filters());
    }

    private function hasEnough(Model $subject, ?Model $scope): bool
    {
        $model = $this->record->model;

        // Asked again here, and not only when the record was written: a model
        // taken off the allowlist afterwards must stop being queried, without
        // anybody having to remember to go and delete the conditions that name it.
        if (!AppModels::isAllowed($model)) {
            return false;
        }

        if (blank($this->record->subject_column)) {
            return false;
        }

        /** @var Builder<Model> $query */
        $query = $model::query()->where($this->record->subject_column, $subject->getKey());

        // A model that is scoped to a tenant only counts within the tenant the
        // subject is being onboarded in — otherwise a user who added a client in
        // one tenant would find the step ticked in all of them.
        if (filled($this->record->scope_column) && $scope instanceof Model) {
            $query->where($this->record->scope_column, $scope->getKey());
        }

        foreach ($this->filters() as $filter) {
            $this->apply($query, $filter);
        }

        return $query->count() >= max(1, $this->record->minimum);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{column: string, operator: ConditionOperator, value: mixed}  $filter
     */
    private function apply(Builder $query, array $filter): void
    {
        $operator = $filter['operator'];
        $column   = $filter['column'];

        if ($operator === ConditionOperator::IsSet) {
            $query->whereNotNull($column);

            return;
        }

        if ($operator === ConditionOperator::IsEmpty) {
            $query->whereNull($column);

            return;
        }

        $value = $filter['value'];

        if ($operator === ConditionOperator::Contains) {
            $query->where($column, 'like', '%' . $value . '%');

            return;
        }

        $query->where($column, $operator->sql(), $this->cast($value));
    }

    /**
     * A form hands everything back as a string. The database does not care much,
     * but "1" against a boolean column is a comparison worth getting right.
     */
    private function cast(mixed $value): mixed
    {
        if (is_string($value) && in_array(mb_strtolower($value), ['true', 'false'], true)) {
            return mb_strtolower($value) === 'true';
        }

        if (is_string($value) && is_numeric($value)) {
            return $value + 0;
        }

        return $value;
    }

    /**
     * The filters, with anything unusable dropped: a column that is not a string,
     * an operator this package does not have. A malformed filter is not a reason
     * to throw — it is a reason not to pass.
     *
     * @return array<int, array{column: string, operator: ConditionOperator, value: mixed}>
     */
    private function filters(): array
    {
        $filters = [];

        foreach ($this->record->filters ?? [] as $filter) {
            if (!is_array($filter) || !is_string($filter['column'] ?? null)) {
                continue;
            }

            $operator = ConditionOperator::tryFrom((string) ($filter['operator'] ?? ''));

            if ($operator === null) {
                continue;
            }

            $filters[] = [
                'column'   => $filter['column'],
                'operator' => $operator,
                'value'    => $filter['value'] ?? null,
            ];
        }

        return $filters;
    }
}
