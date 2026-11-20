<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the roles, then the demo data.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
