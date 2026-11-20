<?php

namespace App\Clock;

use App\Models\Site;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Judges Site days and lateness on the Site's own clock. Timestamps are stored in UTC;
 * which day a Clock belongs to and whether it was late are decided here.
 */
final class SiteTime
{
    /**
     * The Site day (local midnight, in the Site's timezone) that an instant falls on.
     */
    public static function siteDay(Site $site, CarbonInterface $instant): CarbonImmutable
    {
        return CarbonImmutable::instance($instant)->setTimezone($site->timezone)->startOfDay();
    }

    /**
     * When the Site's working day starts on the given Site day, as an instant.
     */
    public static function startOfWork(Site $site, CarbonInterface $siteDay): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $siteDay->format('Y-m-d').' '.$site->start_time,
            $site->timezone,
        );
    }

    /**
     * Whole minutes a Clock-in came after the Site's start time on its Site day; zero when on time.
     */
    public static function lateMinutes(Site $site, CarbonInterface $clockIn): int
    {
        $startOfWork = self::startOfWork($site, self::siteDay($site, $clockIn));
        $secondsLate = $startOfWork->diffInSeconds($clockIn, false);

        return max(0, intdiv((int) $secondsLate, 60));
    }

    /**
     * Whether a Site day is one of the Site's Working days. Holidays are judged by the Register.
     */
    public static function isWorkingDay(Site $site, CarbonInterface $siteDay): bool
    {
        return $site->isWorkingWeekday($siteDay->isoWeekday());
    }
}
