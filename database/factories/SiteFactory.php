<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' HQ',
            'address' => fake()->address(),
            'geofence_lat' => fake()->latitude(1, 6),
            'geofence_lng' => fake()->longitude(100, 104),
            'geofence_radius_m' => config('clockfence.default_radius_m'),
            'start_time' => '09:00:00',
            'timezone' => 'Asia/Kuala_Lumpur',
            'working_days' => Site::DEFAULT_WORKING_DAYS,
        ];
    }

    /**
     * A Site that no longer accepts Clocks.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }
}
