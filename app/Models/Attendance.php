<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\ClockMethod;
use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One Staff member's record for one Site day. Only AttendanceRegister writes it.
 *
 * @property int $id
 * @property int $user_id
 * @property int $site_id
 * @property Carbon $date
 * @property AttendanceStatus $status
 * @property Carbon|null $clock_in_at
 * @property ClockMethod|null $clock_in_method
 * @property Carbon|null $clock_out_at
 * @property ClockMethod|null $clock_out_method
 * @property int $late_minutes
 * @property int|null $recorded_by
 * @property string|null $override_reason
 * @property Carbon|null $edited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Hidden(['clock_in_lat', 'clock_in_lng', 'clock_out_lat', 'clock_out_lng'])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['*'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AttendanceStatus::class,
            'clock_in_at' => 'datetime',
            'clock_in_method' => ClockMethod::class,
            'clock_in_lat' => 'float',
            'clock_in_lng' => 'float',
            'clock_out_at' => 'datetime',
            'clock_out_method' => ClockMethod::class,
            'clock_out_lat' => 'float',
            'clock_out_lng' => 'float',
            'late_minutes' => 'integer',
            'edited_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
