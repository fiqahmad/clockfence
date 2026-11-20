<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clock token
    |--------------------------------------------------------------------------
    |
    | Seconds a Clock token issued by a passing Geo-check stays valid. The
    | Staff member must scan the Poster within this window.
    |
    */

    'token_ttl_seconds' => (int) env('CLOCKFENCE_TOKEN_TTL', 90),

    /*
    |--------------------------------------------------------------------------
    | Geo-check
    |--------------------------------------------------------------------------
    |
    | GPS fixes with an accuracy worse than max_accuracy metres are refused as
    | weak_fix. New Sites start with default_radius metres, and an Admin can
    | set any radius between min_radius and max_radius metres.
    |
    */

    'max_accuracy_m' => (int) env('CLOCKFENCE_MAX_ACCURACY', 100),

    'default_radius_m' => (int) env('CLOCKFENCE_DEFAULT_RADIUS', 120),

    'min_radius_m' => 50,

    'max_radius_m' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Clock-out gap
    |--------------------------------------------------------------------------
    |
    | A Clock-out within this many minutes of the Clock-in is refused as
    | clock_out_too_soon, so a double tap cannot end the day by accident.
    |
    */

    'clock_out_gap_minutes' => (int) env('CLOCKFENCE_CLOCK_OUT_GAP', 5),

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Days before Geo-checks are deleted and Attendance coordinates cleared.
    |
    */

    'retention_days' => (int) env('CLOCKFENCE_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Device binding
    |--------------------------------------------------------------------------
    |
    | When on, a Staff member must Clock from the passkey-bound device.
    |
    */

    'device_binding' => (bool) env('CLOCKFENCE_DEVICE_BINDING', false),

];
