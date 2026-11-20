<?php

namespace App\Clock;

use App\Enums\ClockFailure;
use RuntimeException;

/**
 * A Clock was refused for a named reason the Staff member can act on.
 */
final class ClockRefused extends RuntimeException
{
    public function __construct(public readonly ClockFailure $failure)
    {
        parent::__construct($failure->value);
    }
}
