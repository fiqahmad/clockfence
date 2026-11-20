<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'manager', 'staff'] as $roleName) {
        Role::findOrCreate($roleName);
    }
});

it('lets admins and managers open the admin panel', function (string $roleName) {
    $user = User::factory()->create()->assignRole($roleName);

    $this->actingAs($user)->get('/admin')->assertOk();
})->with(['admin', 'manager']);

it('keeps staff out of the admin panel', function () {
    $user = User::factory()->create()->assignRole('staff');

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
