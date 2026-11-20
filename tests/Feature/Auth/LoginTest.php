<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('shows the login page to guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/Login')->has('copy.title'));
});

it('logs in an active staff member and sends them to the clock page', function () {
    $staffMember = User::factory()->atSite()->withRole(Role::Staff)->create(['password' => 'Secret-123']);

    $this->post('/login', ['email' => $staffMember->email, 'password' => 'Secret-123'])
        ->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($staffMember);

    $this->get('/')->assertRedirect(route('clock.page'));
});

it('sends an admin without a home site to the admin panel', function () {
    $admin = User::factory()->withRole(Role::Admin)->create();

    $this->actingAs($admin)->get('/')->assertRedirect('/admin');
});

it('refuses wrong passwords and inactive or invited users with the same message', function (string $state) {
    $user = User::factory()->atSite()->{$state}()->create(['password' => 'Secret-123']);

    $this->post('/login', ['email' => $user->email, 'password' => 'Secret-123'])
        ->assertSessionHasErrors(['email' => __('auth.failed')]);
    $this->assertGuest();
})->with(['inactive', 'invited']);

it('locks out after five failed attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('sends guests to the login page', function () {
    $this->get('/')->assertRedirect(route('login'));
    $this->get('/clock')->assertRedirect(route('login'));
});

it('logs out', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});
