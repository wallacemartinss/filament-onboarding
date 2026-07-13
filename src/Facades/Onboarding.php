<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Facades;

use Illuminate\Support\Facades\Facade;
use Wallacemartinss\FilamentOnboarding\OnboardingManager;

/**
 * @method static \Wallacemartinss\FilamentOnboarding\OnboardingManager condition(string $key, \Closure|string $condition, ?string $label = null)
 * @method static \Wallacemartinss\FilamentOnboarding\Conditions\ConditionRegistry conditions()
 * @method static \Wallacemartinss\FilamentOnboarding\SubjectOnboarding for(?\Illuminate\Database\Eloquent\Model $subject = null, ?\Illuminate\Database\Eloquent\Model $scope = null)
 * @method static \Wallacemartinss\FilamentOnboarding\SubjectOnboarding|null current()
 * @method static \Wallacemartinss\FilamentOnboarding\OnboardingManager resolveSubjectUsing(?\Closure $callback)
 * @method static \Wallacemartinss\FilamentOnboarding\OnboardingManager resolveScopeUsing(?\Closure $callback)
 * @method static \Wallacemartinss\FilamentOnboarding\OnboardingManager resolveUrlParametersUsing(?\Closure $callback)
 * @method static \Illuminate\Database\Eloquent\Model|null resolveSubject()
 * @method static \Illuminate\Database\Eloquent\Model|null resolveScope()
 * @method static array urlParameters()
 * @method static \Illuminate\Database\Eloquent\Collection flows(?string $panelId = null)
 * @method static \Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow|null flow(string $key, ?string $panelId = null)
 * @method static void flushCache()
 * @method static class-string<\Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow> flowModel()
 * @method static class-string<\Wallacemartinss\FilamentOnboarding\Models\OnboardingStep> stepModel()
 * @method static class-string<\Wallacemartinss\FilamentOnboarding\Models\OnboardingFlowProgress> flowProgressModel()
 * @method static class-string<\Wallacemartinss\FilamentOnboarding\Models\OnboardingStepProgress> stepProgressModel()
 * @method static class-string<\Wallacemartinss\FilamentOnboarding\Models\OnboardingPreference> preferenceModel()
 * @method static class-string<\Wallacemartinss\FilamentOnboarding\Models\OnboardingCondition> conditionModel()
 * @method static array<string, array{condition: \Closure, label: string}> recordedConditions()
 * @method static array<int, string> locales()
 *
 * @see OnboardingManager
 */
class Onboarding extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OnboardingManager::class;
    }
}
