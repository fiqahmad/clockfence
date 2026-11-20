<?php

namespace App\Clock;

use App\Enums\ClockFailure;
use App\Enums\ClockMethod;
use App\Models\GeoCheck;
use App\Models\Site;
use App\Models\User;
use App\Register\AttendanceRegister;
use App\Register\RecordedClock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * The Clock flow, in an order that never changes: a Geo-check issues a Clock token,
 * the camera opens only with that token, and the Poster scan spends it.
 */
final class PhoneClock
{
    private const TOKEN_LENGTH = 48;

    private const ATTEMPTS_PER_MINUTE = 10;

    public function __construct(private readonly AttendanceRegister $register) {}

    /**
     * Test a phone position against the Staff member's home Site and, on a pass, issue a Clock token.
     *
     * @throws ClockRefused
     */
    public function geoCheck(User $staffMember, float $lat, float $lng, float $accuracyMeters): IssuedClockToken
    {
        $this->throttle('geo-check', $staffMember);
        $site = $this->homeSiteOf($staffMember);
        $direction = $this->register->nextDirection($staffMember, $site, now());

        if ($accuracyMeters > (int) config('clockfence.max_accuracy_m')) {
            throw new ClockRefused(ClockFailure::WeakFix);
        }

        $distanceMeters = Geofence::distanceMeters($site->geofence_lat, $site->geofence_lng, $lat, $lng);

        $geoCheck = (new GeoCheck)->forceFill([
            'organization_id' => $staffMember->organization_id,
            'user_id' => $staffMember->id,
            'site_id' => $site->id,
            'distance_m' => round($distanceMeters, 1),
            'accuracy_m' => round($accuracyMeters, 1),
            'radius_m' => $site->geofence_radius_m,
        ]);

        if (! Geofence::passes($distanceMeters, $site->geofence_radius_m)) {
            $geoCheck->forceFill(['passed' => false, 'failure' => ClockFailure::OutsideFence])->save();

            throw new ClockRefused(ClockFailure::OutsideFence);
        }

        $token = Str::random(self::TOKEN_LENGTH);
        $expiresAt = now()->addSeconds((int) config('clockfence.token_ttl_seconds'));

        $geoCheck->forceFill([
            'passed' => true,
            'lat' => $lat,
            'lng' => $lng,
            'token_hash' => self::hash($token),
            'expires_at' => $expiresAt,
        ])->save();

        return new IssuedClockToken($token, $expiresAt->toImmutable(), $direction);
    }

    /**
     * Spend a Clock token on a scanned Poster and record the Clock.
     *
     * @throws ClockRefused
     */
    public function clock(User $staffMember, string $token, string $scannedPoster): RecordedClock
    {
        $this->throttle('clock', $staffMember);
        $geoCheck = $this->spend($token);

        try {
            if ($geoCheck->user_id !== $staffMember->id) {
                throw new ClockRefused(ClockFailure::TokenInvalid);
            }

            if ($geoCheck->hasExpired()) {
                throw new ClockRefused(ClockFailure::TokenExpired);
            }

            $posterSiteId = SitePoster::siteIdFrom($scannedPoster);

            if ($posterSiteId === null) {
                throw new ClockRefused(ClockFailure::PosterUnreadable);
            }

            if ($posterSiteId !== $geoCheck->site_id) {
                throw new ClockRefused(ClockFailure::WrongSite);
            }

            $site = $this->homeSiteOf($staffMember);

            if ($site->id !== $geoCheck->site_id) {
                throw new ClockRefused(ClockFailure::WrongSite);
            }

            return $this->register->recordClock($staffMember, $site, now(), ClockMethod::QrGeofence, $geoCheck->lat, $geoCheck->lng);
        } finally {
            $geoCheck->forceFill(['lat' => null, 'lng' => null])->save();
        }
    }

    /**
     * Spend the token with one conditional UPDATE before any other check, so a token
     * later refused is still spent and two requests with one token cannot both win.
     *
     * @throws ClockRefused
     */
    private function spend(string $token): GeoCheck
    {
        $tokenHash = self::hash($token);

        $spent = DB::table('geo_checks')
            ->where('token_hash', $tokenHash)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now(), 'updated_at' => now()]);

        if ($spent !== 1) {
            throw new ClockRefused(ClockFailure::TokenInvalid);
        }

        return GeoCheck::query()->where('token_hash', $tokenHash)->sole();
    }

    /**
     * The Site is always resolved from the Staff member's record, never from the request.
     *
     * @throws ClockRefused
     */
    private function homeSiteOf(User $staffMember): Site
    {
        if (! $staffMember->isActive()) {
            throw new ClockRefused(ClockFailure::AccountInactive);
        }

        $site = $staffMember->site;

        if ($site === null || $site->isArchived()) {
            throw new ClockRefused(ClockFailure::NoHomeSite);
        }

        return $site;
    }

    /**
     * @throws ClockRefused
     */
    private function throttle(string $action, User $staffMember): void
    {
        $allowed = RateLimiter::attempt(
            "clock:{$action}:{$staffMember->id}",
            self::ATTEMPTS_PER_MINUTE,
            fn (): bool => true,
        );

        if ($allowed === false) {
            throw new ClockRefused(ClockFailure::TooManyAttempts);
        }
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
