<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Enums\StaffStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('clockfence:install
    {--name= : Name of the first Admin}
    {--email= : Email of the first Admin}
    {--password= : Password of the first Admin}
    {--force : Create another Admin even if one exists}')]
#[Description('Create the roles and the first Admin')]
class InstallClockFence extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->callSilently('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);

        if (! $this->option('force') && User::role(Role::Admin->value)->exists()) {
            $this->components->error('An Admin already exists. Pass --force to create another one.');

            return self::FAILURE;
        }

        $adminDetails = [
            'name' => $this->option('name') ?? text('Admin name', required: true),
            'email' => $this->option('email') ?? text('Admin email', required: true),
            'password' => $this->option('password') ?? password('Admin password', required: true),
        ];

        $validator = Validator::make($adminDetails, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        $admin = User::create([...$validator->validated(), 'status' => StaffStatus::Active]);
        $admin->assignRole(Role::Admin->value);

        $this->components->info("Admin {$admin->email} created. Log in at ".url('/admin').'.');
        $this->components->warn('Create a second Admin or a Manager too: nobody can override their own Attendance.');

        return self::SUCCESS;
    }
}
