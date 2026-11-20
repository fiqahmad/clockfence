<?php

return [
    'label' => 'Staf',
    'plural' => 'Staf',
    'fields' => [
        'name' => 'Nama',
        'email' => 'E-mel',
        'staff_no' => 'No. staf',
        'site' => 'Tapak utama',
        'site_help' => 'Biarkan kosong untuk Admin yang tidak merekod kehadiran.',
        'role' => 'Peranan',
        'locale' => 'Bahasa',
        'status' => 'Status',
    ],
    'status' => [
        'invited' => 'Dijemput',
        'active' => 'Aktif',
        'inactive' => 'Tidak aktif',
    ],
    'roles' => [
        'admin' => 'Admin',
        'manager' => 'Pengurus',
        'staff' => 'Staf',
    ],
    'locales' => [
        'en' => 'English',
        'ms' => 'Bahasa Melayu',
    ],
    'actions' => [
        'deactivate' => 'Nyahaktifkan',
        'deactivate_confirm' => 'Mereka tidak lagi boleh log masuk atau merekod kehadiran. Sejarah mereka disimpan.',
        'deactivated' => 'Staf dinyahaktifkan.',
        'reactivate' => 'Aktifkan semula',
        'reactivated' => 'Staf diaktifkan semula.',
        'resend_invitation' => 'Hantar semula jemputan',
        'invitation_sent' => 'Jemputan dihantar.',
    ],
];
