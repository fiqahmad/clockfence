<?php

namespace App\Clock;

use App\Enums\ClockDirection;
use Carbon\CarbonImmutable;

/**
 * The raw Clock token, handed to the phone once and never stored.
 */
final readonly class IssuedClockToken
{
    public function __construct(
        public string $token,
        public CarbonImmutable $expiresAt,
        public ClockDirection $direction,
    ) {}
}
