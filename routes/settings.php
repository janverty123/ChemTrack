<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [SettingsController::class, 'profile'])->middleware('throttle:10,1,settings-profile');
    Route::patch('/preferences', [SettingsController::class, 'preferences'])->middleware('throttle:30,1,settings-preferences');
    Route::put('/password', [SettingsController::class, 'password'])->middleware('throttle:5,1,settings-password');
    Route::post('/email', [SettingsController::class, 'requestEmail'])->middleware('throttle:3,1,settings-email');
    Route::get('/email/confirm/{user}/{token}', [SettingsController::class, 'reviewEmail'])->middleware('signed')->name('email.confirm');
    Route::post('/email/confirm/{user}/{token}', [SettingsController::class, 'confirmEmail'])->middleware(['signed', 'throttle:5,1,settings-email-confirm']);
    Route::post('/sessions/revoke', [SettingsController::class, 'revokeSessions'])->middleware('throttle:5,1,settings-sessions');
    Route::get('/avatar', [SettingsController::class, 'avatar'])->name('avatar');
    Route::post('/avatar', [SettingsController::class, 'saveAvatar'])->middleware('throttle:10,1,settings-avatar');
    Route::delete('/avatar', [SettingsController::class, 'deleteAvatar']);
});
