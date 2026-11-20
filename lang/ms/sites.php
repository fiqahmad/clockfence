<?php

return [
    'label' => 'Tapak',
    'plural' => 'Tapak',
    'sections' => [
        'details' => 'Butiran',
        'fence' => 'Pagar geo',
        'fence_help' => 'Titik tempat staf berdiri untuk merekod kehadiran, biasanya pintu masuk. Dalam Google Maps, klik kanan pada tempat itu dan salin latitud dan longitud.',
        'schedule' => 'Waktu bekerja',
    ],
    'fields' => [
        'name' => 'Nama',
        'address' => 'Alamat',
        'geofence_lat' => 'Latitud',
        'geofence_lng' => 'Longitud',
        'geofence_radius_m' => 'Jejari (meter)',
        'start_time' => 'Waktu mula',
        'timezone' => 'Zon waktu',
        'working_days' => 'Hari bekerja',
        'staff_count' => 'Staf',
        'archived' => 'Diarkibkan',
    ],
    'weekdays' => [
        1 => 'Isnin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Khamis',
        5 => 'Jumaat',
        6 => 'Sabtu',
        7 => 'Ahad',
    ],
    'actions' => [
        'archive' => 'Arkibkan',
        'unarchive' => 'Keluarkan dari arkib',
        'archive_confirm' => 'Tapak ini menyimpan sejarahnya tetapi posternya tidak lagi berfungsi.',
        'archived' => 'Tapak diarkibkan.',
        'unarchived' => 'Tapak dikeluarkan dari arkib.',
        'cannot_archive' => 'Pindahkan atau nyahaktifkan staf aktif tapak ini dahulu.',
    ],
    'filters' => [
        'archived' => 'Diarkibkan',
    ],
];
