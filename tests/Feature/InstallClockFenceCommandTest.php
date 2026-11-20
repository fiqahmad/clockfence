<?php

use App\Enums\Role;
use App\Models\User;

it('creates the roles and the first admin', function () {
    $this->artisan('clockfence:install', [
        '--name' => 'First Admin',
        '--email' => 'first@example.com',
        '--password' => 'secret-password',
    ])->assertSuccessful();

    $admin = User::where('email', 'first@example.com')->sole();

    expect($admin->hasRole(Role::Admin->value))->toBeTrue()
        ->and($admin->canAccessPanel(filament()->getPanel('admin')))->toBeTrue();
});

it('refuses to create a second admin without force', function () {
    $options = ['--name' => 'First Admin', '--email' => 'first@example.com', '--password' => 'secret-password'];
    $this->artisan('clockfence:install', $options)->assertSuccessful();

    $this->artisan('clockfence:install', [...$options, '--email' => 'second@example.com'])->assertFailed();
    $this->artisan('clockfence:install', [...$options, '--email' => 'second@example.com', '--force' => true])->assertSuccessful();

    expect(User::role(Role::Admin->value)->count())->toBe(2);
});

it('rejects an invalid email or short password', function (array $badOption) {
    $this->artisan('clockfence:install', [
        '--name' => 'First Admin',
        '--email' => 'first@example.com',
        '--password' => 'secret-password',
        ...$badOption,
    ])->assertFailed();

    expect(User::count())->toBe(0);
})->with([
    'invalid email' => [['--email' => 'not-an-email']],
    'short password' => [['--password' => 'short']],
]);
