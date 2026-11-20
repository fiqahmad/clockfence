<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('lets admins and managers open the admin panel', function (Role $role) {
    $user = User::factory()->withRole($role)->create();

    $this->actingAs($user)->get('/admin')->assertOk();
})->with(Role::panelRoles());

it('keeps staff out of the admin panel', function () {
    $user = User::factory()->withRole(Role::Staff)->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
