<?php

namespace App\Register;

use App\Enums\ClockDirection;
use App\Models\Attendance;

/**
 * A Clock the Register accepted, and the Attendance it landed on.
 */
final readonly class RecordedClock
{
    public function __construct(
        public Attendance $attendance,
        public ClockDirection $direction,
    ) {}
}
