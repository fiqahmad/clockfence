<?php

use App\Clock\SiteTime;
use App\Models\Site;
use Carbon\CarbonImmutable;

function siteIn(string $timezone, string $startTime = '09:00:00'): Site
{
    return new Site([
        'timezone' => $timezone,
        'start_time' => $startTime,
        'working_days' => Site::DEFAULT_WORKING_DAYS,
    ]);
}

it('puts a Clock on the Site day in the Site timezone, not UTC', function (string $timezone, string $utcInstant, string $expectedDay) {
    $siteDay = SiteTime::siteDay(siteIn($timezone), CarbonImmutable::parse($utcInstant, 'UTC'));

    expect($siteDay->format('Y-m-d'))->toBe($expectedDay)
        ->and($siteDay->format('H:i'))->toBe('00:00');
})->with([
    'east of UTC, after local midnight' => ['Asia/Kuala_Lumpur', '2026-11-19 16:30:00', '2026-11-20'],
    'east of UTC, before local midnight' => ['Asia/Kuala_Lumpur', '2026-11-19 15:59:00', '2026-11-19'],
    'west of UTC, still yesterday locally' => ['America/New_York', '2026-11-20 03:00:00', '2026-11-19'],
    'just after local midnight' => ['Asia/Kuala_Lumpur', '2026-11-19 16:00:30', '2026-11-20'],
]);

it('counts whole late minutes after the start time, with no grace period', function (string $localClockIn, int $expectedMinutes) {
    $site = siteIn('Asia/Kuala_Lumpur');
    $clockIn = CarbonImmutable::parse($localClockIn, 'Asia/Kuala_Lumpur')->utc();

    expect(SiteTime::lateMinutes($site, $clockIn))->toBe($expectedMinutes);
})->with([
    'early' => ['2026-11-20 08:45:00', 0],
    'exactly on time' => ['2026-11-20 09:00:00', 0],
    'under a minute late' => ['2026-11-20 09:00:59', 0],
    'one minute late' => ['2026-11-20 09:01:00', 1],
    'two hours late' => ['2026-11-20 11:00:00', 120],
]);

it('judges lateness on local wall-clock time across a DST change', function () {
    $site = siteIn('Europe/London', '09:00:00');

    $summerClockIn = CarbonImmutable::parse('2026-07-01 09:10:00', 'Europe/London')->utc();
    $winterClockIn = CarbonImmutable::parse('2026-12-01 09:10:00', 'Europe/London')->utc();

    expect(SiteTime::lateMinutes($site, $summerClockIn))->toBe(10)
        ->and(SiteTime::lateMinutes($site, $winterClockIn))->toBe(10);
});

it('knows Working days and Rest days', function (string $day, bool $isWorkingDay) {
    $site = siteIn('Asia/Kuala_Lumpur');

    expect(SiteTime::isWorkingDay($site, CarbonImmutable::parse($day, 'Asia/Kuala_Lumpur')))->toBe($isWorkingDay);
})->with([
    'Friday' => ['2026-11-20', true],
    'Saturday' => ['2026-11-21', false],
    'Sunday' => ['2026-11-22', false],
    'Monday' => ['2026-11-23', true],
]);
