<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Auth\Pages\Login;
use Livewire\Livewire;

/*
| The smoke group is what an installer (human or agent) runs to know the
| install works: `php artisan test --group=smoke`.
*/

pest()->group('smoke');

it('boots and serves the home page', function () {
    $this->get('/')->assertOk();
});

it('has no pending migrations', function () {
    $this->artisan('migrate:status')->doesntExpectOutputToContain('Pending')->assertSuccessful();
});

it('lets an admin log in to the admin panel', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->withRole(Role::Admin)->create(['password' => 'secret-password']);

    Livewire::test(Login::class)
        ->fillForm(['email' => $admin->email, 'password' => 'secret-password'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($admin);
    $this->get('/admin')->assertOk();
});
