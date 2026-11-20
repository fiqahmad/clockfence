<?php

return [
    'label' => 'Staff member',
    'plural' => 'Staff',
    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'staff_no' => 'Staff number',
        'site' => 'Home Site',
        'site_help' => 'Leave empty for an Admin who does not Clock.',
        'role' => 'Role',
        'locale' => 'Language',
        'status' => 'Status',
    ],
    'status' => [
        'invited' => 'Invited',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'roles' => [
        'admin' => 'Admin',
        'manager' => 'Manager',
        'staff' => 'Staff',
    ],
    'locales' => [
        'en' => 'English',
        'ms' => 'Bahasa Melayu',
    ],
    'actions' => [
        'deactivate' => 'Deactivate',
        'deactivate_confirm' => 'They will no longer be able to log in or Clock. Their history is kept.',
        'deactivated' => 'Staff member deactivated.',
        'reactivate' => 'Reactivate',
        'reactivated' => 'Staff member reactivated.',
        'resend_invitation' => 'Resend invitation',
        'invitation_sent' => 'Invitation sent.',
    ],
];
