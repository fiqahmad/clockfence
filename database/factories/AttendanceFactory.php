<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Enums\ClockMethod;
use App\Models\Attendance;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * An on-time Clock-in today.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_id' => Site::factory(),
            'date' => now()->toDateString(),
            'status' => AttendanceStatus::Present,
            'clock_in_at' => now(),
            'clock_in_method' => ClockMethod::QrGeofence,
            'late_minutes' => 0,
        ];
    }
}
