<?php

use App\Enums\Role;
use App\Filament\Resources\Sites\Pages\CreateSite;
use App\Filament\Resources\Sites\Pages\EditSite;
use App\Filament\Resources\Sites\Pages\ListSites;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function validSiteData(array $overrides = []): array
{
    return [
        'name' => 'Riverside Office',
        'address' => '1 River Road',
        'geofence_lat' => 3.1579,
        'geofence_lng' => 101.7116,
        'geofence_radius_m' => 120,
        'start_time' => '08:30',
        'timezone' => 'Asia/Kuala_Lumpur',
        'working_days' => [1, 2, 3, 4, 5],
        ...$overrides,
    ];
}

it('lets an admin create a site', function () {
    $this->actingAs(User::factory()->withRole(Role::Admin)->create());

    Livewire::test(CreateSite::class)
        ->fillForm(validSiteData())
        ->call('create')
        ->assertHasNoFormErrors();

    $site = Site::sole();

    expect($site->name)->toBe('Riverside Office')
        ->and($site->geofence_radius_m)->toBe(120)
        ->and($site->working_days)->toBe([1, 2, 3, 4, 5])
        ->and($site->timezone)->toBe('Asia/Kuala_Lumpur');
});

it('keeps the radius between the configured bounds', function (int $radius, bool $valid) {
    $this->actingAs(User::factory()->withRole(Role::Admin)->create());

    $component = Livewire::test(CreateSite::class)
        ->fillForm(validSiteData(['geofence_radius_m' => $radius]))
        ->call('create');

    $valid ? $component->assertHasNoFormErrors() : $component->assertHasFormErrors(['geofence_radius_m']);
})->with([
    'below minimum' => [49, false],
    'minimum' => [50, true],
    'maximum' => [1000, true],
    'above maximum' => [1001, false],
]);

it('rejects an unknown timezone and impossible coordinates', function () {
    $this->actingAs(User::factory()->withRole(Role::Admin)->create());

    Livewire::test(CreateSite::class)
        ->fillForm(validSiteData(['timezone' => 'Mars/Olympus', 'geofence_lat' => 91, 'geofence_lng' => -181]))
        ->call('create')
        ->assertHasFormErrors(['timezone', 'geofence_lat', 'geofence_lng']);
});

it('lets managers see sites but not create or edit them', function () {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->withRole(Role::Manager)->create());

    Livewire::test(ListSites::class)->assertCanSeeTableRecords([$site]);
    $this->get(CreateSite::getUrl())->assertForbidden();
    $this->get(EditSite::getUrl(['record' => $site]))->assertForbidden();
});

it('archives a site only when no active staff member calls it home', function () {
    $this->actingAs(User::factory()->withRole(Role::Admin)->create());
    $site = Site::factory()->create();
    $activeStaffMember = User::factory()->atSite($site)->create();

    Livewire::test(ListSites::class)->callTableAction('archive', $site);
    expect($site->fresh()->isArchived())->toBeFalse();

    $activeStaffMember->update(['status' => 'inactive']);

    Livewire::test(ListSites::class)->callTableAction('archive', $site);
    expect($site->fresh()->isArchived())->toBeTrue();

    Livewire::test(ListSites::class)
        ->filterTable('archived', true)
        ->callTableAction('unarchive', $site);
    expect($site->fresh()->isArchived())->toBeFalse();
});

it('never offers to delete a site', function () {
    $user = User::factory()->withRole(Role::Admin)->create();
    $site = Site::factory()->create();

    expect($user->can('delete', $site))->toBeFalse()
        ->and($user->can('deleteAny', Site::class))->toBeFalse();
});
