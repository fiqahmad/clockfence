<?php

use App\Clock\Geofence;

it('measures zero between a point and itself', function () {
    expect(Geofence::distanceMeters(3.1579, 101.7116, 3.1579, 101.7116))->toBe(0.0);
});

it('measures known distances', function (array $from, array $to, float $expectedMeters, float $toleranceMeters) {
    expect(Geofence::distanceMeters(...$from, ...$to))->toEqualWithDelta($expectedMeters, $toleranceMeters);
})->with([
    'one degree of latitude' => [[0.0, 0.0], [1.0, 0.0], 111_195.0, 5.0],
    'one degree of longitude at the equator' => [[0.0, 0.0], [0.0, 1.0], 111_195.0, 5.0],
    'one degree of longitude at 60 degrees north is half' => [[60.0, 0.0], [60.0, 1.0], 55_597.0, 5.0],
    'about 100 m north in Kuala Lumpur' => [[3.1579, 101.7116], [3.1588, 101.7116], 100.1, 0.5],
    'across the antimeridian' => [[0.0, 179.9995], [0.0, -179.9995], 111.2, 0.5],
    'pole to pole' => [[90.0, 0.0], [-90.0, 0.0], 20_015_115.0, 50.0],
]);

it('is symmetric', function () {
    expect(Geofence::distanceMeters(3.1579, 101.7116, 1.2834, 103.8607))
        ->toEqualWithDelta(Geofence::distanceMeters(1.2834, 103.8607, 3.1579, 101.7116), 0.001);
});

it('passes on or inside the radius and fails outside it', function (float $distance, bool $passes) {
    expect(Geofence::passes($distance, 120))->toBe($passes);
})->with([
    'inside' => [119.9, true],
    'on the edge' => [120.0, true],
    'just outside' => [120.1, false],
]);
