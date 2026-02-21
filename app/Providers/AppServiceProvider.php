<?php

namespace App\Providers;

use App\Events\Glucose\GlucoseReadingCritical;
use App\Events\Glucose\GlucoseReadingRecorded;
use App\Events\Rules\MedicationSuggestionGenerated;
use App\Listeners\Glucose\EvaluateGlucoseRulesListener;
use App\Listeners\Glucose\HandleGlucoseReadingCritical;
use App\Listeners\Rules\PersistMedicationSuggestionListener;
use App\RuleEngine\Contracts\RuleEngineInterface;
use App\RuleEngine\RuleEngine;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RuleEngineInterface::class, RuleEngine::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(GlucoseReadingCritical::class, HandleGlucoseReadingCritical::class);
        Event::listen(GlucoseReadingRecorded::class, EvaluateGlucoseRulesListener::class);
        Event::listen(MedicationSuggestionGenerated::class, PersistMedicationSuggestionListener::class);
    }
}
