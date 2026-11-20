<?php

namespace App\Http\Resources;

use App\Clock\IssuedClockToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property IssuedClockToken $resource
 */
class ClockTokenResource extends JsonResource
{
    /**
     * The raw token goes to the phone once. No distance or position is ever included.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource->token,
            'expires_in' => max(0, (int) now()->diffInSeconds($this->resource->expiresAt)),
            'direction' => $this->resource->direction->value,
        ];
    }
}
