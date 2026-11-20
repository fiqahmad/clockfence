<?php

return [
    'mail' => [
        'subject' => 'You are invited to :app',
        'greeting' => 'Hello :name,',
        'intro' => 'You have been added to :app to record your attendance. Set a password to get started.',
        'action' => 'Set my password',
        'expiry' => 'This link works for :days days. If it has expired, ask your Admin to send a new one.',
    ],
    'page' => [
        'title' => 'Set your password',
        'intro' => 'Welcome! Choose a password to finish setting up your account.',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'submit' => 'Set password',
        'used' => 'This invitation has already been used. Log in instead, or ask your Admin for help.',
    ],
];
