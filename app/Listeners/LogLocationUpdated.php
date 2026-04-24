<?php

namespace App\Listeners;

use App\Events\LocationUpdated;
use Illuminate\Support\Facades\Log;

class LogLocationUpdated
{
    public function handle(LocationUpdated $event): void
    {
        Log::info('User location updated', [
            'user_id' => $event->location->user_id,
            'latitude' => $event->location->latitude,
            'longitude' => $event->location->longitude,
            'accuracy' => $event->location->accuracy,
            'updated_at' => $event->location->updated_at?->toIso8601String(),
        ]);
    }
}
