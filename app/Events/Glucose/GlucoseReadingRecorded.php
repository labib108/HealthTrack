<?php

namespace App\Events\Glucose;

use App\Models\GlucoseReading;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Published after any glucose reading is persisted (create or update).
 * Triggers rule evaluation, analytics projections, and audit.
 */
class GlucoseReadingRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GlucoseReading $reading
    ) {
    }
}
