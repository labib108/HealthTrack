<?php

namespace App\Events\Glucose;

use App\Models\GlucoseReading;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GlucoseReadingCritical
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GlucoseReading $reading
    ) {
    }
}
