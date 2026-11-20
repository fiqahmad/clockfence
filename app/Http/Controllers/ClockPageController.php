<?php

namespace App\Http\Controllers;

use App\Clock\ClockRefused;
use App\Enums\ClockFailure;
use App\Models\User;
use App\Register\AttendanceRegister;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The staff clock page: a light Blade view driven by public/js/clock.js (ADR 0001).
 */
class ClockPageController extends Controller
{
    public function __invoke(Request $request, AttendanceRegister $register): View
    {
        /** @var User $staffMember */
        $staffMember = $request->user();
        $site = $staffMember->site;

        return view('clock', [
            'staffMember' => $staffMember,
            'site' => $site,
            'canOpenAdmin' => $staffMember->canAccessPanel(filament()->getPanel('admin')),
            'clockConfig' => [
                'state' => $this->stateFor($staffMember, $register),
                'urls' => [
                    'geoCheck' => route('clock.geo-check'),
                    'clock' => route('clock.store'),
                ],
                'copy' => __('clock.page'),
                'failures' => ClockFailure::catalogue(),
                'locale' => app()->getLocale(),
                'timezone' => $site?->timezone,
            ],
        ]);
    }

    /**
     * What the page offers right now: 'in', 'out', 'done', 'no_site' or 'inactive'.
     */
    private function stateFor(User $staffMember, AttendanceRegister $register): string
    {
        if (! $staffMember->isActive()) {
            return 'inactive';
        }

        $site = $staffMember->site;

        if ($site === null || $site->isArchived()) {
            return 'no_site';
        }

        try {
            return $register->nextDirection($staffMember, $site, now())->value;
        } catch (ClockRefused $refusal) {
            return $refusal->failure === ClockFailure::AlreadyClockedOut ? 'done' : 'out';
        }
    }
}
