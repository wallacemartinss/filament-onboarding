<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Tables;

use Filament\Actions\{ActionGroup, BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, ViewAction};
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Table;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;

class OnboardingFlowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                    ->state(fn (OnboardingFlow $record): ?string => $record->translate('title'))
                    ->description(fn (OnboardingFlow $record): string => $record->key)
                    ->icon(fn (OnboardingFlow $record): ?string => $record->icon)
                    ->iconColor(fn (OnboardingFlow $record): string => $record->color ?: 'primary')
                    ->weight(FontWeight::Medium)
                    ->searchable(['key'])
                    ->sortable(),

                TextColumn::make('steps_count')
                    ->label(__('filament-onboarding::onboarding.resource.fields.steps'))
                    ->counts('steps')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'danger')
                    ->tooltip(fn (int $state): ?string => $state === 0
                        ? __('filament-onboarding::onboarding.resource.no_steps_warning')
                        : null)
                    ->alignCenter(),

                TextColumn::make('panel_id')
                    ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                    ->badge()
                    ->color('gray')
                    ->placeholder(__('filament-onboarding::onboarding.resource.all_panels'))
                    ->visibleFrom('md'),

                TextColumn::make('visibility_condition')
                    ->label(__('filament-onboarding::onboarding.resource.fields.visibility'))
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (string $state): string => Onboarding::conditions()->options()[$state] ?? $state)
                    ->placeholder(__('filament-onboarding::onboarding.resource.fields.visibility_everyone'))
                    ->visibleFrom('lg'),

                IconColumn::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label(__('filament-onboarding::onboarding.resource.fields.updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active')),

                SelectFilter::make('panel_id')
                    ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                    ->options(fn (Table $table): array => $table->getQuery()
                        ->getModel()::query()
                        ->whereNotNull('panel_id')
                        ->distinct()
                        ->pluck('panel_id', 'panel_id')
                        ->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon(Heroicon::OutlinedEye),
                    EditAction::make()
                        ->icon(Heroicon::OutlinedPencilSquare),
                    DeleteAction::make()
                        ->icon(Heroicon::OutlinedTrash),
                ])->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedMap)
            ->emptyStateHeading(__('filament-onboarding::onboarding.resource.empty.heading'))
            ->emptyStateDescription(__('filament-onboarding::onboarding.resource.empty.description'));
    }
}
