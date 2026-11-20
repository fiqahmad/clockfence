<?php

use App\Enums\ClockMethod;
use App\Models\Attendance;
use App\Models\Site;
use App\Models\User;

function clockConfigFrom(string $html): array
{
    preg_match('/<script type="application\/json" id="clock-config">(.*?)<\/script>/s', $html, $matches);

    return json_decode(html_entity_decode($matches[1]), true, flags: JSON_THROW_ON_ERROR);
}

beforeEach(function () {
    $this->site = Site::factory()->create(['name' => 'Riverside Office', 'geofence_lat' => 3.1579, 'geofence_lng' => 101.7116]);
    $this->staffMember = User::factory()->atSite($this->site)->create(['name' => 'Siti']);
});

it('offers a clock-in first, with the failure catalogue and the clock script', function () {
    $response = $this->actingAs($this->staffMember)->get('/clock')
        ->assertOk()
        ->assertSee('Riverside Office')
        ->assertSee('/js/clock.js', escape: false)
        ->assertSee('/manifest.webmanifest', escape: false);

    $config = clockConfigFrom($response->getContent());

    expect($config['state'])->toBe('in')
        ->and($config['failures'])->toHaveCount(16)
        ->and($config['urls']['geoCheck'])->toEndWith('/clock/geo-check')
        ->and($config['timezone'])->toBe($this->site->timezone);
});

it('offers a clock-out after a clock-in, then nothing once done', function () {
    $attendance = Attendance::factory()->create([
        'user_id' => $this->staffMember->id,
        'site_id' => $this->site->id,
        'date' => now($this->site->timezone)->toDateString(),
        'clock_in_at' => now()->subHours(8),
    ]);

    expect(clockConfigFrom($this->actingAs($this->staffMember)->get('/clock')->getContent())['state'])->toBe('out');

    $attendance->forceFill(['clock_out_at' => now(), 'clock_out_method' => ClockMethod::QrGeofence])->save();

    expect(clockConfigFrom($this->get('/clock')->getContent())['state'])->toBe('done');
});

it('tells a user without a home site that they cannot clock', function () {
    $this->staffMember->update(['site_id' => null]);

    expect(clockConfigFrom($this->actingAs($this->staffMember)->get('/clock')->getContent())['state'])->toBe('no_site');
});

it('never puts coordinates or the fence radius on the page', function () {
    $html = $this->actingAs($this->staffMember)->get('/clock')->getContent();

    expect($html)->not->toContain('3.1579')
        ->not->toContain('101.7116')
        ->not->toContain('radius');
});

it('speaks the staff member language', function () {
    $this->staffMember->update(['locale' => 'ms']);

    $this->actingAs($this->staffMember)->get('/clock')
        ->assertSee(__('clock.page.greeting', ['name' => 'Siti'], 'ms'));
});

it('serves the web app manifest and the clock script', function () {
    expect(file_exists(public_path('manifest.webmanifest')))->toBeTrue()
        ->and(json_decode(file_get_contents(public_path('manifest.webmanifest')), true)['start_url'])->toBe('/clock')
        ->and(file_get_contents(public_path('js/clock.js')))->toContain('getCurrentPosition')
        ->not->toContain('watchPosition');
});

it('points the page at the built jsQR fallback for browsers without BarcodeDetector', function () {
    $config = clockConfigFrom($this->actingAs($this->staffMember)->get('/clock')->getContent());

    expect($config['urls']['qrFallback'])->toContain('/build/assets/qr-fallback')
        ->and(file_get_contents(public_path('js/clock.js')))->toContain('config.urls.qrFallback');
});
