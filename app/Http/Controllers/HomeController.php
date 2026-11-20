<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Send each User where they work: Staff members to the clock page, others to the admin panel.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->site_id === null && $user->canAccessPanel(filament()->getPanel('admin'))) {
            return redirect('/admin');
        }

        return redirect()->route('clock.page');
    }
}
