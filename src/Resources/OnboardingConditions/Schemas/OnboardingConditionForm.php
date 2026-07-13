<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Schemas;

use Filament\Forms\Components\{Repeater, Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Components\{Grid, Section, Tabs};
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Enums\{ConditionOperator, ConditionType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Support\AppModels;

/**
 * Writing the question, without writing code.
 *
 * "Has at least one client." "Has three clients that are active." "Has verified
 * their email." All of them are the same two shapes, and both fit in a form —
 * which is the difference between a journey that ships with a deploy and one that
 * ships with a click.
 */
class OnboardingConditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                self::questionSection(),
                self::namingSection(),
            ]);
    }

    protected static function questionSection(): Section
    {
        return Section::make(__('filament-onboarding::onboarding.conditions.sections.question'))
            ->description(__('filament-onboarding::onboarding.conditions.sections.question_description'))
            ->icon(Heroicon::OutlinedQuestionMarkCircle)
            ->schema([
                Select::make('type')
                    ->label(__('filament-onboarding::onboarding.conditions.fields.type'))
                    ->options(ConditionType::class)
                    ->default(ConditionType::Aggregate)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->live()
                    ->required()
                    ->columnSpanFull(),

                // ── Aggregate: count rows of another model ──────────────────────
                Grid::make(3)
                    ->visible(fn (Get $get): bool => $get('type') === ConditionType::Aggregate->value)
                    ->schema([
                        Select::make('model')
                            ->label(__('filament-onboarding::onboarding.conditions.fields.model'))
                            ->helperText(__('filament-onboarding::onboarding.conditions.fields.model_helper'))
                            ->prefixIcon(Heroicon::OutlinedCircleStack)
                            ->options(fn (): array => AppModels::options())
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->required(fn (Get $get): bool => $get('type') === ConditionType::Aggregate->value)
                            ->columnSpan(2),

                        TextInput::make('minimum')
                            ->label(__('filament-onboarding::onboarding.conditions.fields.minimum'))
                            ->helperText(__('filament-onboarding::onboarding.conditions.fields.minimum_helper'))
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ]),

                Grid::make(2)
                    ->visible(fn (Get $get): bool => $get('type') === ConditionType::Aggregate->value && filled($get('model')))
                    ->schema([
                        Select::make('subject_column')
                            ->label(__('filament-onboarding::onboarding.conditions.fields.subject_column'))
                            ->helperText(__('filament-onboarding::onboarding.conditions.fields.subject_column_helper'))
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->options(fn (Get $get): array => AppModels::foreignKeys($get('model')))
                            ->default('user_id')
                            ->searchable()
                            ->native(false)
                            ->required(fn (Get $get): bool => $get('type') === ConditionType::Aggregate->value),

                        Select::make('scope_column')
                            ->label(__('filament-onboarding::onboarding.conditions.fields.scope_column'))
                            ->helperText(__('filament-onboarding::onboarding.conditions.fields.scope_column_helper'))
                            ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                            ->options(fn (Get $get): array => AppModels::foreignKeys($get('model')))
                            ->placeholder(__('filament-onboarding::onboarding.conditions.fields.scope_column_none'))
                            ->searchable()
                            ->native(false),
                    ]),

                // ── Filters, for either shape ───────────────────────────────────
                Repeater::make('filters')
                    ->label(fn (Get $get): string => $get('type') === ConditionType::Attribute->value
                        ? __('filament-onboarding::onboarding.conditions.fields.filters_attribute')
                        : __('filament-onboarding::onboarding.conditions.fields.filters'))
                    ->helperText(fn (Get $get): string => $get('type') === ConditionType::Attribute->value
                        ? __('filament-onboarding::onboarding.conditions.fields.filters_attribute_helper')
                        : __('filament-onboarding::onboarding.conditions.fields.filters_helper'))
                    ->addActionLabel(__('filament-onboarding::onboarding.conditions.fields.add_filter'))
                    ->visible(fn (Get $get): bool => $get('type') === ConditionType::Attribute->value || filled($get('model')))
                    ->defaultItems(fn (Get $get): int => $get('type') === ConditionType::Attribute->value ? 1 : 0)
                    ->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => filled($state['column'] ?? null)
                        ? trim(($state['column'] ?? '') . ' ' . Str::lower((string) (ConditionOperator::tryFrom((string) ($state['operator'] ?? ''))?->getLabel() ?? '')) . ' ' . ($state['value'] ?? ''))
                        : null)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('column')
                                ->label(__('filament-onboarding::onboarding.conditions.fields.column'))
                                ->options(fn (Get $get): array => $get('../../type') === ConditionType::Attribute->value
                                    ? AppModels::subjectColumns()
                                    : AppModels::columns($get('../../model')))
                                ->searchable()
                                ->native(false)
                                ->required(),

                            Select::make('operator')
                                ->label(__('filament-onboarding::onboarding.conditions.fields.operator'))
                                ->options(ConditionOperator::class)
                                ->default(ConditionOperator::Equals)
                                ->selectablePlaceholder(false)
                                ->native(false)
                                ->live()
                                ->required(),

                            TextInput::make('value')
                                ->label(__('filament-onboarding::onboarding.conditions.fields.value'))
                                ->visible(fn (Get $get): bool => ConditionOperator::tryFrom((string) $get('operator'))?->needsValue() ?? true)
                                ->required(fn (Get $get): bool => ConditionOperator::tryFrom((string) $get('operator'))?->needsValue() ?? true),
                        ]),
                    ]),
            ]);
    }

    protected static function namingSection(): Section
    {
        $locales = Onboarding::locales();

        return Section::make(__('filament-onboarding::onboarding.conditions.sections.naming'))
            ->description(__('filament-onboarding::onboarding.conditions.sections.naming_description'))
            ->icon(Heroicon::OutlinedTag)
            ->schema([
                Tabs::make('translations')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs(
                        collect($locales)
                            ->map(fn (string $locale): Tab => Tab::make($locale)
                                ->label(Str::upper(str_replace('_', '-', $locale)))
                                ->schema([
                                    TextInput::make("label.{$locale}")
                                        ->label(__('filament-onboarding::onboarding.conditions.fields.label'))
                                        ->helperText(__('filament-onboarding::onboarding.conditions.fields.label_helper'))
                                        ->placeholder(__('filament-onboarding::onboarding.conditions.placeholders.label'))
                                        ->required($locale === $locales[0])
                                        ->maxLength(255),

                                    Textarea::make("description.{$locale}")
                                        ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                        ->rows(2)
                                        ->maxLength(500),
                                ]))
                            ->all()
                    ),

                Grid::make(2)->schema([
                    TextInput::make('key')
                        ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                        ->helperText(__('filament-onboarding::onboarding.conditions.fields.key_helper'))
                        ->prefixIcon(Heroicon::OutlinedKey)
                        ->placeholder('has_client')
                        ->required()
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        // Code registered a `has_server`? Then this cannot be one.
                        // A row quietly shadowing a class is how a step comes to
                        // mean something other than what it says.
                        ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                            if (in_array($value, Onboarding::conditions()->codeKeys(), true)) {
                                $fail(__('filament-onboarding::onboarding.conditions.fields.key_taken', ['key' => $value]));
                            }
                        })
                        ->maxLength(255),

                    Toggle::make('is_active')
                        ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                        ->helperText(__('filament-onboarding::onboarding.conditions.fields.is_active_helper'))
                        ->default(true),
                ]),
            ]);
    }
}
