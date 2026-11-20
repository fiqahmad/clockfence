<?php

namespace App\Register;

use App\Clock\ClockRefused;
use App\Clock\SiteTime;
use App\Enums\AttendanceStatus;
use App\Enums\ClockDirection;
use App\Enums\ClockFailure;
use App\Enums\ClockMethod;
use App\Models\Attendance;
use App\Models\Site;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The only class that writes Attendances. The first Clock of a Site day is a
 * Clock-in, the next a Clock-out, and any further one is refused.
 */
final class AttendanceRegister
{
    /**
     * Which Clock comes next for this Staff member at this moment, or why none can.
     *
     * @throws ClockRefused
     */
    public function nextDirection(User $staffMember, Site $site, CarbonInterface $at): ClockDirection
    {
        return $this->directionFor($this->attendanceOn($staffMember, $site, $at), $at);
    }

    /**
     * Record a Clock and return the Attendance it landed on.
     *
     * @throws ClockRefused
     */
    public function recordClock(
        User $staffMember,
        Site $site,
        CarbonInterface $at,
        ClockMethod $method,
        ?float $lat = null,
        ?float $lng = null,
    ): RecordedClock {
        try {
            return $this->recordClockOnce($staffMember, $site, $at, $method, $lat, $lng);
        } catch (UniqueConstraintViolationException) {
            // Another Clock created today's Attendance at the same moment; this one now lands on it.
            return $this->recordClockOnce($staffMember, $site, $at, $method, $lat, $lng);
        }
    }

    private function recordClockOnce(
        User $staffMember,
        Site $site,
        CarbonInterface $at,
        ClockMethod $method,
        ?float $lat,
        ?float $lng,
    ): RecordedClock {
        return DB::transaction(function () use ($staffMember, $site, $at, $method, $lat, $lng): RecordedClock {
            $attendance = $this->attendanceOn($staffMember, $site, $at, lockForUpdate: true);
            $direction = $this->directionFor($attendance, $at);

            if ($direction === ClockDirection::In) {
                $attendance ??= $this->newAttendance($staffMember, $site, $at);
                $this->applyClockIn($attendance, $site, $at, $method, $lat, $lng);
            } else {
                /** @var Attendance $attendance */
                $attendance->forceFill([
                    'clock_out_at' => $at,
                    'clock_out_method' => $method,
                    'clock_out_lat' => $lat,
                    'clock_out_lng' => $lng,
                ]);
            }

            $attendance->save();

            return new RecordedClock($attendance, $direction);
        });
    }

    /**
     * @throws ClockRefused
     */
    private function directionFor(?Attendance $attendance, CarbonInterface $at): ClockDirection
    {
        if ($attendance === null || $attendance->clock_in_at === null) {
            return ClockDirection::In;
        }

        if ($attendance->clock_out_at !== null) {
            throw new ClockRefused(ClockFailure::AlreadyClockedOut);
        }

        $earliestClockOut = CarbonImmutable::instance($attendance->clock_in_at)
            ->addMinutes((int) config('clockfence.clock_out_gap_minutes'));

        if ($at->lessThan($earliestClockOut)) {
            throw new ClockRefused(ClockFailure::ClockOutTooSoon);
        }

        return ClockDirection::Out;
    }

    private function attendanceOn(User $staffMember, Site $site, CarbonInterface $at, bool $lockForUpdate = false): ?Attendance
    {
        $query = Attendance::query()
            ->where('user_id', $staffMember->id)
            ->whereDate('date', SiteTime::siteDay($site, $at)->toDateString());

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function newAttendance(User $staffMember, Site $site, CarbonInterface $at): Attendance
    {
        return (new Attendance)->forceFill([
            'organization_id' => $staffMember->organization_id,
            'user_id' => $staffMember->id,
            'site_id' => $site->id,
            'date' => SiteTime::siteDay($site, $at)->toDateString(),
        ]);
    }

    private function applyClockIn(Attendance $attendance, Site $site, CarbonInterface $at, ClockMethod $method, ?float $lat, ?float $lng): void
    {
        $isWorkingDay = SiteTime::isWorkingDay($site, SiteTime::siteDay($site, $at))
            && $attendance->status !== AttendanceStatus::Holiday;
        $lateMinutes = $isWorkingDay ? SiteTime::lateMinutes($site, $at) : 0;

        $attendance->forceFill([
            'status' => $lateMinutes > 0 ? AttendanceStatus::Late : AttendanceStatus::Present,
            'late_minutes' => $lateMinutes,
            'clock_in_at' => $at,
            'clock_in_method' => $method,
            'clock_in_lat' => $lat,
            'clock_in_lng' => $lng,
        ]);
    }
}
