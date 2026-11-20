<?php

return [
    'failures' => [
        'insecure_context' => 'This page must be opened over a secure link (https://). Please tell your Admin.',
        'location_denied' => 'Location is blocked for this page. Allow location in your browser settings, then try again.',
        'location_unavailable' => 'Your phone could not find its location. Turn on location, step near a window or outside, and try again.',
        'location_timeout' => 'Finding your location took too long. Make sure location is on and try again.',
        'weak_fix' => 'Your location is not precise enough yet. Wait a few seconds with location on, then try again.',
        'outside_fence' => 'You are not at your Site yet. Go to the entrance of your Site and try again.',
        'no_home_site' => 'You have no home Site yet. Ask your Admin to assign one.',
        'account_inactive' => 'Your account is not active. Please contact your Admin.',
        'too_many_attempts' => 'Too many tries in a short time. Wait a minute, then try again.',
        'camera_denied' => 'The camera is blocked for this page. Allow the camera in your browser settings, then try again.',
        'poster_unreadable' => 'That code is not a ClockFence poster. Scan the poster at your Site entrance.',
        'wrong_site' => 'That poster belongs to another Site. Scan the poster at your own Site, starting again.',
        'token_expired' => 'Too much time passed before the scan. Start again and scan the poster straight away.',
        'token_invalid' => 'This clock attempt can no longer be used. Start again.',
        'already_clocked_out' => 'You have already clocked in and out today. If this is wrong, ask a Manager.',
        'clock_out_too_soon' => 'You clocked in only a moment ago. Wait a few minutes before clocking out, or ask a Manager if this is a mistake.',
    ],
];
