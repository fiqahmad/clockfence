<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for a fictional company. Never put real people or workplaces here.
 */
class DemoSeeder extends Seeder
{
    /**
     * @var list<array{name: string, email: string, role: Role}>
     */
    private const DEMO_USERS = [
        ['name' => 'Aina Admin', 'email' => 'admin@demo.clockfence.test', 'role' => Role::Admin],
        ['name' => 'Malik Manager', 'email' => 'manager@demo.clockfence.test', 'role' => Role::Manager],
        ['name' => 'Siti Staff', 'email' => 'siti@demo.clockfence.test', 'role' => Role::Staff],
        ['name' => 'Ravi Staff', 'email' => 'ravi@demo.clockfence.test', 'role' => Role::Staff],
        ['name' => 'Mei Ling Staff', 'email' => 'meiling@demo.clockfence.test', 'role' => Role::Staff],
    ];

    public const DEMO_PASSWORD = 'password';

    /**
     * Seed the demo Users and print their logins.
     */
    public function run(): void
    {
        foreach (self::DEMO_USERS as $demoUser) {
            User::factory()
                ->withRole($demoUser['role'])
                ->create(['name' => $demoUser['name'], 'email' => $demoUser['email']]);
        }

        $this->command?->table(
            ['Role', 'Email', 'Password'],
            array_map(
                fn (array $demoUser): array => [$demoUser['role']->value, $demoUser['email'], self::DEMO_PASSWORD],
                self::DEMO_USERS,
            ),
        );
    }
}
