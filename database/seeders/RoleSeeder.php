<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as PermissionRole;

class RoleSeeder extends Seeder
{
    /**
     * Create every role in the Role enum. Safe to run more than once.
     */
    public function run(): void
    {
        foreach (Role::cases() as $role) {
            PermissionRole::findOrCreate($role->value);
        }
    }
}
