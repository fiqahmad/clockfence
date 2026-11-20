<?php

use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['guest', 'signed'])->group(function () {
    Route::get('/invitations/{user}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{user}', [InvitationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('invitations.store');
});
