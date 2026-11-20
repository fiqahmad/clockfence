<?php

return [
    'label' => 'Site',
    'plural' => 'Sites',
    'sections' => [
        'details' => 'Details',
        'fence' => 'Geofence',
        'fence_help' => 'The point staff stand at to Clock, usually the entrance. In Google Maps, right-click the spot and copy the latitude and longitude.',
        'schedule' => 'Working hours',
    ],
    'fields' => [
        'name' => 'Name',
        'address' => 'Address',
        'geofence_lat' => 'Latitude',
        'geofence_lng' => 'Longitude',
        'geofence_radius_m' => 'Radius (metres)',
        'start_time' => 'Start time',
        'timezone' => 'Timezone',
        'working_days' => 'Working days',
        'staff_count' => 'Staff',
        'archived' => 'Archived',
    ],
    'weekdays' => [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ],
    'actions' => [
        'poster' => 'Poster',
        'archive' => 'Archive',
        'unarchive' => 'Unarchive',
        'archive_confirm' => 'The Site keeps its history but its Poster stops working.',
        'archived' => 'Site archived.',
        'unarchived' => 'Site unarchived.',
        'cannot_archive' => 'Move or deactivate this Site\'s Active Staff members first.',
    ],
    'filters' => [
        'archived' => 'Archived',
    ],
];
