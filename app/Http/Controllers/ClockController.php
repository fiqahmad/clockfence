<?php

namespace App\Http\Controllers;

use App\Clock\ClockRefused;
use App\Clock\PhoneClock;
use App\Http\Requests\ClockRequest;
use App\Http\Requests\GeoCheckRequest;
use App\Http\Resources\ClockFailureResource;
use App\Http\Resources\ClockTokenResource;
use App\Http\Resources\RecordedClockResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Session JSON endpoints for the clock page. All rules live in PhoneClock.
 */
class ClockController extends Controller
{
    public function __construct(private readonly PhoneClock $phoneClock) {}

    public function geoCheck(GeoCheckRequest $request): JsonResponse
    {
        /** @var User $staffMember */
        $staffMember = $request->user();

        try {
            $issuedToken = $this->phoneClock->geoCheck(
                $staffMember,
                (float) $request->validated('lat'),
                (float) $request->validated('lng'),
                (float) $request->validated('accuracy'),
            );
        } catch (ClockRefused $refusal) {
            return ClockFailureResource::make($refusal->failure)->response($request);
        }

        return ClockTokenResource::make($issuedToken)->response($request);
    }

    public function store(ClockRequest $request): JsonResponse
    {
        /** @var User $staffMember */
        $staffMember = $request->user();

        try {
            $recordedClock = $this->phoneClock->clock(
                $staffMember,
                (string) $request->validated('token'),
                (string) $request->validated('poster'),
            );
        } catch (ClockRefused $refusal) {
            return ClockFailureResource::make($refusal->failure)->response($request);
        }

        return RecordedClockResource::make($recordedClock)->response($request)->setStatusCode(201);
    }
}
