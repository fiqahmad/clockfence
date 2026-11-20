<?php

namespace App\Http\Resources;

use App\Enums\ClockFailure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ClockFailure $resource
 */
class ClockFailureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'failure' => $this->resource->value,
            'message' => $this->resource->message(),
            'retryable' => $this->resource->retryable(),
        ];
    }

    /**
     * 429 for rate limiting, 422 for every other refusal.
     */
    public function toResponse($request): JsonResponse
    {
        return parent::toResponse($request)->setStatusCode(
            $this->resource === ClockFailure::TooManyAttempts ? 429 : 422,
        );
    }
}
