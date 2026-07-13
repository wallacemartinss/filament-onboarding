<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Tables;

use Filament\Actions\{ActionGroup, BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Enums\ConditionType;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingCondition;

class OnboardingConditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('label')
                    ->label(__('filament-onboarding::onboarding.conditions.fields.label'))
                    ->state(fn (OnboardingCondition $record): ?string => $record->translate('label'))
                    ->description(fn (OnboardingCondition $record): string => $record->key)
                    ->weight(FontWeight::Medium)
                    ->searchable(['key'])
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('filament-onboarding::onboarding.conditions.fields.type'))
                    ->badge(),

                // What it actually asks, in a line: "Client · at least 1 · status = active".
                TextColumn::make('question')
                    ->label(__('filament-onboarding::onboarding.conditions.fields.question'))
                    ->state(fn (OnboardingCondition $record): string => static::describe($record))
                    ->color('gray')
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->boolean()
                    ->alignCenter(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->icon(Heroicon::OutlinedPencilSquare),
                    DeleteAction::make()->icon(Heroicon::OutlinedTrash),
                ])->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedBolt)
            ->emptyStateHeading(__('filament-onboarding::onboarding.conditions.empty.heading'))
            ->emptyStateDescription(__('filament-onboarding::onboarding.conditions.empty.description'));
    }

    /**
     * The question in one line, so the list reads as a list of questions rather
     * than of names.
     */
    private static function describe(OnboardingCondition $record): string
    {
        $filters = collect($record->filters ?? [])
            ->filter(fn ($filter): bool => is_array($filter) && filled($filter['column'] ?? null))
            ->map(function (array $filter): string {
                $operator = \Wallacemartinss\FilamentOnboarding\Enums\ConditionOperator::tryFrom((string) ($filter['operator'] ?? ''));

                return trim(sprintf(
                    '%s %s %s',
                    $filter['column'],
                    Str::lower((string) ($operator?->getLabel() ?? '')),
                    ($operator?->needsValue() ?? true) ? (string) ($filter['value'] ?? '') : '',
                ));
            });

        if ($record->type === ConditionType::Attribute) {
            return $filters->implode(', ') ?: '—';
        }

        return collect([
            filled($record->model) ? class_basename($record->model) : '—',
            __('filament-onboarding::onboarding.conditions.at_least', ['count' => max(1, $record->minimum)]),
            ...$filters->all(),
        ])->implode(' · ');
    }
}
