<?php

namespace App\Enums;

/**
 * How a Clock was recorded.
 */
enum ClockMethod: string
{
    case QrGeofence = 'qr_geofence';
    case ManualAdmin = 'manual_admin';
}
