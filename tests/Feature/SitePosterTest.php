<?php

use App\Clock\SitePoster;
use App\Enums\Role;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->site = Site::factory()->create(['name' => 'Riverside Office']);
});

it('renders a printable poster with an SVG QR code for admins and managers', function (Role $role) {
    $this->actingAs(User::factory()->withRole($role)->create())
        ->get(route('sites.poster', $this->site))
        ->assertOk()
        ->assertSee('Riverside Office')
        ->assertSee('<svg', escape: false)
        ->assertSee(__('posters.title', locale: 'ms'));
})->with(Role::panelRoles());

it('keeps staff and guests away from posters', function () {
    $this->actingAs(User::factory()->withRole(Role::Staff)->create())
        ->get(route('sites.poster', $this->site))
        ->assertForbidden();

    auth()->logout();
    $this->get(route('sites.poster', $this->site))->assertRedirect(route('login'));
});

it('has no poster for an archived site', function () {
    $this->site->archive();

    $this->actingAs(User::factory()->withRole(Role::Admin)->create())
        ->get(route('sites.poster', $this->site))
        ->assertGone();
});

it('encodes the site id and nothing secret', function () {
    expect(SitePoster::payload($this->site))->toBe('CLOCKFENCE:'.$this->site->id)
        ->and(SitePoster::siteIdFrom(' CLOCKFENCE:'.$this->site->id.' '))->toBe($this->site->id)
        ->and(SitePoster::siteIdFrom('CLOCKFENCE:0'))->toBeNull()
        ->and(SitePoster::siteIdFrom('clockfence:1'))->toBeNull();
});
