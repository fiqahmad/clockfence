<?php

namespace Database\Factories;

use App\Models\GeoCheck;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeoCheck>
 */
class GeoCheckFactory extends Factory
{
    /**
     * A passing Geo-check without a token.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_id' => Site::factory(),
            'passed' => true,
            'distance_m' => fake()->randomFloat(1, 0, 100),
            'accuracy_m' => fake()->randomFloat(1, 5, 50),
            'radius_m' => 120,
        ];
    }
}
