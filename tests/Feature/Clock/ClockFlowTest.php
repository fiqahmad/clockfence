<?php

use App\Clock\SitePoster;
use App\Enums\AttendanceStatus;
use App\Enums\ClockMethod;
use App\Models\Attendance;
use App\Models\GeoCheck;
use App\Models\Site;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Testing\TestResponse;

const SITE_LAT = 3.1579;
const SITE_LNG = 101.7116;

beforeEach(function () {
    $this->site = Site::factory()->create([
        'geofence_lat' => SITE_LAT,
        'geofence_lng' => SITE_LNG,
        'geofence_radius_m' => 120,
        'start_time' => '09:00:00',
        'timezone' => 'Asia/Kuala_Lumpur',
        'working_days' => [1, 2, 3, 4, 5],
    ]);
    $this->staffMember = User::factory()->atSite($this->site)->create();
    travelToLocal('2026-11-20 08:55:00'); // a Friday
});

function geoCheck(array $overrides = []): TestResponse
{
    return test()->actingAs(test()->staffMember)->postJson('/clock/geo-check', [
        'lat' => SITE_LAT,
        'lng' => SITE_LNG,
        'accuracy' => 15,
        ...$overrides,
    ]);
}

function scan(string $token, ?string $poster = null): TestResponse
{
    return test()->actingAs(test()->staffMember)->postJson('/clock', [
        'token' => $token,
        'poster' => $poster ?? SitePoster::payload(test()->site),
    ]);
}

function travelToLocal(string $localTime): void
{
    test()->travelTo(CarbonImmutable::parse($localTime, 'Asia/Kuala_Lumpur'));
}

function issuedToken(): string
{
    return geoCheck()->assertOk()->json('data.token');
}

// ---------------------------------------------------------------- Geo-check

it('issues a single-use token on a passing geo-check', function () {
    $response = geoCheck()->assertOk();

    expect($response->json('data.token'))->toHaveLength(48)
        ->and($response->json('data.expires_in'))->toBe(90)
        ->and($response->json('data.direction'))->toBe('in');

    $geoCheck = GeoCheck::sole();
    expect($geoCheck->passed)->toBeTrue()
        ->and($geoCheck->token_hash)->toBe(hash('sha256', $response->json('data.token')))
        ->and($geoCheck->distance_m)->toBe(0.0);
});

it('never sends distance or coordinates to the phone', function () {
    $body = geoCheck()->getContent().geoCheck(['lat' => SITE_LAT + 0.01])->getContent();

    expect($body)->not->toContain('distance')
        ->not->toContain('lat')
        ->not->toContain((string) SITE_LNG);
});

it('refuses a position outside the fence, logs the distance and keeps no position', function () {
    geoCheck(['lat' => SITE_LAT + 0.01])
        ->assertStatus(422)
        ->assertJsonPath('data.failure', 'outside_fence')
        ->assertJsonPath('data.retryable', true);

    $geoCheck = GeoCheck::sole();
    expect($geoCheck->passed)->toBeFalse()
        ->and($geoCheck->distance_m)->toBeGreaterThan(1000)
        ->and($geoCheck->lat)->toBeNull()
        ->and($geoCheck->lng)->toBeNull()
        ->and($geoCheck->token_hash)->toBeNull();
});

it('refuses a weak GPS fix', function () {
    geoCheck(['accuracy' => 101])->assertStatus(422)->assertJsonPath('data.failure', 'weak_fix');

    expect(GeoCheck::count())->toBe(0);
});

it('resolves the site from the staff member, never the request', function () {
    $otherSite = Site::factory()->create(['geofence_lat' => 1.3, 'geofence_lng' => 103.8]);

    geoCheck(['lat' => 1.3, 'lng' => 103.8, 'site_id' => $otherSite->id])
        ->assertStatus(422)
        ->assertJsonPath('data.failure', 'outside_fence');
});

it('refuses a staff member without a home site or who is inactive', function () {
    $this->staffMember->update(['site_id' => null]);
    geoCheck()->assertJsonPath('data.failure', 'no_home_site');

    $this->staffMember->update(['site_id' => $this->site->id]);
    $this->staffMember->deactivate();
    geoCheck()->assertJsonPath('data.failure', 'account_inactive');
});

it('rate limits geo-checks', function () {
    foreach (range(1, 10) as $attempt) {
        geoCheck(['lat' => SITE_LAT + 0.01]);
    }

    geoCheck()->assertStatus(429)->assertJsonPath('data.failure', 'too_many_attempts');
});

it('requires a logged-in user', function () {
    $this->postJson('/clock/geo-check', ['lat' => SITE_LAT, 'lng' => SITE_LNG, 'accuracy' => 10])->assertUnauthorized();
});

// ---------------------------------------------------------------- Clock

it('clocks in on time, then clocks out, through the full flow', function () {
    scan(issuedToken())->assertCreated()
        ->assertJsonPath('data.direction', 'in')
        ->assertJsonPath('data.status', 'present')
        ->assertJsonPath('data.late_minutes', 0);

    travelToLocal('2026-11-20 17:30:00');
    expect(geoCheck()->json('data.direction'))->toBe('out');

    scan(issuedToken())->assertCreated()->assertJsonPath('data.direction', 'out');

    $attendance = Attendance::sole();
    expect($attendance->date->toDateString())->toBe('2026-11-20')
        ->and($attendance->clock_in_method)->toBe(ClockMethod::QrGeofence)
        ->and($attendance->clock_out_method)->toBe(ClockMethod::QrGeofence)
        ->and($attendance->clock_in_at->toIso8601String())->toBe('2026-11-20T00:55:00+00:00');
});

it('records late minutes on a working day and none on a rest day', function () {
    travelToLocal('2026-11-20 09:17:30');
    scan(issuedToken())->assertJsonPath('data.status', 'late')->assertJsonPath('data.late_minutes', 17);

    travelToLocal('2026-11-21 11:00:00'); // Saturday
    scan(issuedToken())->assertJsonPath('data.status', 'present')->assertJsonPath('data.late_minutes', 0);
});

it('keeps the clock coordinates for audit but clears them from the geo-check once spent', function () {
    scan(issuedToken())->assertCreated();

    expect(GeoCheck::sole()->lat)->toBeNull()
        ->and(Attendance::sole()->getAttributes()['clock_in_lat'])->not->toBeNull()
        ->and(Attendance::sole()->toArray())->not->toHaveKey('clock_in_lat');
});

it('spends a token only once, so a replay is refused', function () {
    $token = issuedToken();

    scan($token)->assertCreated();
    travelToLocal('2026-11-20 17:30:00');
    scan($token)->assertStatus(422)->assertJsonPath('data.failure', 'token_invalid');

    expect(Attendance::sole()->clock_out_at)->toBeNull();
});

it('lets only one of two requests with the same token win', function () {
    $token = issuedToken();

    $responses = [scan($token), scan($token)];

    expect(collect($responses)->filter(fn (TestResponse $response) => $response->status() === 201))->toHaveCount(1)
        ->and(Attendance::count())->toBe(1);
});

it('refuses an expired token and still spends it', function () {
    $token = issuedToken();
    $this->travel(91)->seconds();

    scan($token)->assertJsonPath('data.failure', 'token_expired');
    scan($token)->assertJsonPath('data.failure', 'token_invalid');

    expect(Attendance::count())->toBe(0);
});

it('refuses another site poster and spends the token', function () {
    $otherSite = Site::factory()->create();
    $token = issuedToken();

    scan($token, SitePoster::payload($otherSite))->assertJsonPath('data.failure', 'wrong_site');
    scan($token)->assertJsonPath('data.failure', 'token_invalid');

    expect(Attendance::count())->toBe(0);
});

it('refuses a code that is not a poster', function (string $payload) {
    scan(issuedToken(), $payload)->assertJsonPath('data.failure', 'poster_unreadable');

    expect(Attendance::count())->toBe(0);
})->with(['https://example.com', 'CLOCKFENCE:', 'CLOCKFENCE:abc', 'CLOCKFENCE:-1']);

it('refuses a token issued to someone else', function () {
    $token = issuedToken();
    $colleague = User::factory()->atSite($this->site)->create();

    $this->actingAs($colleague)
        ->postJson('/clock', ['token' => $token, 'poster' => SitePoster::payload($this->site)])
        ->assertJsonPath('data.failure', 'token_invalid');

    expect(Attendance::count())->toBe(0);
});

it('refuses a clock-out too soon after the clock-in', function () {
    scan(issuedToken())->assertCreated();
    $this->travel(4)->minutes();

    geoCheck()->assertJsonPath('data.failure', 'clock_out_too_soon');
});

it('refuses a third clock on the same day', function () {
    scan(issuedToken())->assertCreated();
    travelToLocal('2026-11-20 17:30:00');
    scan(issuedToken())->assertCreated();

    geoCheck()->assertJsonPath('data.failure', 'already_clocked_out')->assertJsonPath('data.retryable', false);
});

it('starts a new attendance after midnight in the site timezone', function () {
    scan(issuedToken())->assertCreated();
    travelToLocal('2026-11-21 00:05:00');

    expect(geoCheck()->json('data.direction'))->toBe('in');
});

it('writes no attendance for any refused clock', function () {
    geoCheck(['lat' => SITE_LAT + 0.01]);
    geoCheck(['accuracy' => 500]);
    scan('not-a-real-token');

    expect(Attendance::count())->toBe(0)
        ->and(Attendance::query()->where('status', AttendanceStatus::Present)->exists())->toBeFalse();
});
