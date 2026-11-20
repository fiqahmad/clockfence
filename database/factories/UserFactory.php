<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Enums\StaffStatus;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => StaffStatus::Active,
            'locale' => 'en',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Give the User a role once created.
     */
    public function withRole(Role $role): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole($role->value));
    }

    /**
     * A Staff member with a home Site.
     */
    public function atSite(?Site $site = null): static
    {
        return $this->state(fn (array $attributes) => [
            'site_id' => $site ?? Site::factory(),
            'staff_no' => fake()->unique()->numerify('S####'),
        ]);
    }

    /**
     * A Staff member who has been invited but has not set a password yet.
     */
    public function invited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StaffStatus::Invited,
        ]);
    }

    /**
     * A Staff member who can no longer log in or Clock.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StaffStatus::Inactive,
        ]);
    }
}
