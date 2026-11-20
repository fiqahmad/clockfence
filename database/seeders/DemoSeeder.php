<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for a fictional company. Never put real people or workplaces here.
 */
class DemoSeeder extends Seeder
{
    /**
     * Two Sites at public landmarks in Kuala Lumpur.
     *
     * @var array<string, array{name: string, address: string, geofence_lat: float, geofence_lng: float}>
     */
    private const DEMO_SITES = [
        'hq' => ['name' => 'Demo HQ (KLCC Park)', 'address' => 'KLCC Park, Kuala Lumpur', 'geofence_lat' => 3.1550, 'geofence_lng' => 101.7140],
        'branch' => ['name' => 'Demo Branch (Merdeka Square)', 'address' => 'Dataran Merdeka, Kuala Lumpur', 'geofence_lat' => 3.1478, 'geofence_lng' => 101.6939],
    ];

    /**
     * @var list<array{name: string, email: string, role: Role, site: string|null}>
     */
    private const DEMO_USERS = [
        ['name' => 'Aina Admin', 'email' => 'admin@demo.clockfence.test', 'role' => Role::Admin, 'site' => null],
        ['name' => 'Malik Manager', 'email' => 'manager@demo.clockfence.test', 'role' => Role::Manager, 'site' => 'hq'],
        ['name' => 'Siti Staff', 'email' => 'siti@demo.clockfence.test', 'role' => Role::Staff, 'site' => 'hq'],
        ['name' => 'Ravi Staff', 'email' => 'ravi@demo.clockfence.test', 'role' => Role::Staff, 'site' => 'hq'],
        ['name' => 'Mei Ling Staff', 'email' => 'meiling@demo.clockfence.test', 'role' => Role::Staff, 'site' => 'branch'],
    ];

    public const DEMO_PASSWORD = 'password';

    /**
     * Seed the demo Sites and Users and print the logins.
     */
    public function run(): void
    {
        $sites = array_map(
            fn (array $demoSite): Site => Site::factory()->create([...$demoSite, 'start_time' => '09:00:00', 'timezone' => 'Asia/Kuala_Lumpur']),
            self::DEMO_SITES,
        );

        foreach (self::DEMO_USERS as $staffNumber => $demoUser) {
            $factory = User::factory()->withRole($demoUser['role']);

            if ($demoUser['site'] !== null) {
                $factory = $factory->atSite($sites[$demoUser['site']]);
            }

            $factory->create([
                'name' => $demoUser['name'],
                'email' => $demoUser['email'],
                'staff_no' => sprintf('DEMO%03d', $staffNumber + 1),
            ]);
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
