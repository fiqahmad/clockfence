<?php

namespace App\Http\Resources;

use App\Register\RecordedClock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RecordedClock $resource
 */
class RecordedClockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendance = $this->resource->attendance;

        return [
            'direction' => $this->resource->direction->value,
            'status' => $attendance->status->value,
            'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
            'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
            'late_minutes' => $attendance->late_minutes,
        ];
    }
}
