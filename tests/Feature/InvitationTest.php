<?php

use App\Enums\Role;
use App\Enums\StaffStatus;
use App\Models\User;
use App\Notifications\StaffInvitation;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->invitee = User::factory()->withRole(Role::Staff)->invited()->create(['email_verified_at' => null]);
});

function invitationUrl(User $user): string
{
    return URL::temporarySignedRoute('invitations.show', now()->addDay(), ['user' => $user]);
}

it('emails a signed link to set a password', function () {
    $mail = (new StaffInvitation)->toMail($this->invitee);

    expect($mail->actionUrl)->toContain('/invitations/'.$this->invitee->id)
        ->and($mail->actionUrl)->toContain('signature=');
});

it('shows the set-password page for a valid link', function () {
    $this->get(invitationUrl($this->invitee))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Invitations/Accept')
            ->where('email', $this->invitee->email)
            ->has('acceptUrl'));
});

it('rejects an unsigned or tampered link', function () {
    $this->get('/invitations/'.$this->invitee->id)->assertForbidden();
    $this->get(invitationUrl($this->invitee).'x')->assertForbidden();
});

it('sets the password, activates and logs in', function () {
    $acceptUrl = URL::temporarySignedRoute('invitations.store', now()->addHour(), ['user' => $this->invitee]);

    $this->post($acceptUrl, ['password' => 'Long-enough-1', 'password_confirmation' => 'Long-enough-1'])
        ->assertRedirect('/');

    $this->invitee->refresh();
    expect($this->invitee->status)->toBe(StaffStatus::Active)
        ->and(Hash::check('Long-enough-1', $this->invitee->password))->toBeTrue()
        ->and($this->invitee->email_verified_at)->not->toBeNull();
    $this->assertAuthenticatedAs($this->invitee);
});

it('requires a confirmed password', function () {
    $acceptUrl = URL::temporarySignedRoute('invitations.store', now()->addHour(), ['user' => $this->invitee]);

    $this->post($acceptUrl, ['password' => 'Long-enough-1', 'password_confirmation' => 'different'])
        ->assertSessionHasErrors('password');

    expect($this->invitee->fresh()->status)->toBe(StaffStatus::Invited);
});

it('cannot be used twice', function () {
    $this->invitee->forceFill(['status' => StaffStatus::Active])->save();

    $this->get(invitationUrl($this->invitee))->assertGone();
});

it('cannot reactivate an inactive staff member', function () {
    $this->invitee->deactivate();
    $acceptUrl = URL::temporarySignedRoute('invitations.store', now()->addHour(), ['user' => $this->invitee]);

    $this->post($acceptUrl, ['password' => 'Long-enough-1', 'password_confirmation' => 'Long-enough-1'])->assertGone();

    expect($this->invitee->fresh()->status)->toBe(StaffStatus::Inactive);
});
