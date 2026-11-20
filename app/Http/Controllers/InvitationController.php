<?php

namespace App\Http\Controllers;

use App\Enums\StaffStatus;
use App\Http\Requests\AcceptInvitationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

/**
 * An Invited Staff member sets their password through a signed link and becomes Active.
 */
class InvitationController extends Controller
{
    public function show(Request $request, User $user): Response
    {
        abort_unless($user->status === StaffStatus::Invited, 410, __('invitations.page.used'));

        return Inertia::render('Invitations/Accept', [
            'name' => $user->name,
            'email' => $user->email,
            'acceptUrl' => URL::temporarySignedRoute(
                'invitations.store',
                now()->addHour(),
                ['user' => $user],
            ),
            'copy' => __('invitations.page'),
        ]);
    }

    public function store(AcceptInvitationRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->status === StaffStatus::Invited, 410, __('invitations.page.used'));

        $user->forceFill([
            'password' => $request->validated('password'),
            'status' => StaffStatus::Active,
            'email_verified_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->canAccessPanel(filament()->getPanel('admin')) ? '/admin' : '/');
    }
}
