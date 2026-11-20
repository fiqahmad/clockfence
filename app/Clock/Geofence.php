<?php

namespace App\Clock;

/**
 * Distance on the Earth's surface and the geofence pass rule.
 */
final class Geofence
{
    /**
     * Mean Earth radius in metres (IUGG).
     */
    private const EARTH_RADIUS_M = 6_371_008.8;

    /**
     * Great-circle distance in metres between two points, by the Haversine formula.
     */
    public static function distanceMeters(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $haversine = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_M * asin(min(1.0, sqrt($haversine)));
    }

    /**
     * A Geo-check passes when the distance is within the radius. GPS accuracy is not added or subtracted.
     */
    public static function passes(float $distanceMeters, int $radiusMeters): bool
    {
        return $distanceMeters <= $radiusMeters;
    }
}
