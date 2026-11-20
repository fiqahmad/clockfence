<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role as PermissionRole;

it('creates every role and can run twice', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(PermissionRole::pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::cases())->map->value->sort()->values()->all());
});

it('seeds one admin, one manager and three staff on demo domains', function () {
    $this->seed();

    expect(User::role(Role::Admin->value)->count())->toBe(1)
        ->and(User::role(Role::Manager->value)->count())->toBe(1)
        ->and(User::role(Role::Staff->value)->count())->toBe(3)
        ->and(User::pluck('email')->every(fn (string $email) => str_ends_with($email, '@demo.clockfence.test')))->toBeTrue();
});
