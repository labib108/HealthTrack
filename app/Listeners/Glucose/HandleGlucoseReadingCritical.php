<?php

namespace App\Listeners\Glucose;

use App\Events\Glucose\GlucoseReadingCritical;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleGlucoseReadingCritical implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * Event-driven alerting: no alert logic in controllers or models.
     * This listener can be extended to push notifications, SMS, email,
     * care-team alerts, or physician dashboard integrations.
     */
    public function handle(GlucoseReadingCritical $event): void
    {
        $reading = $event->reading;

        Log::warning('Glucose reading requires alert', [
            'reading_id' => $reading->id,
            'user_id' => $reading->user_id,
            'value_mg_dl' => $reading->value_mg_dl,
            'clinical_classification' => $reading->clinical_classification?->value,
            'measured_at' => $reading->measured_at?->toIso8601String(),
        ]);

        // Future: send push notification, SMS, email, care-team alert
        // $this->notificationService->alertUser($reading->user, $reading);
    }
}
