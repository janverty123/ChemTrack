<?php

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StudentInvitationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register/verify', [RegistrationController::class, 'verifyPage']);
    Route::post('/register/verify', [RegistrationController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('/register/resend', [RegistrationController::class, 'resend'])->middleware('throttle:5,1');
    Route::get('/register/profile', [RegistrationController::class, 'profile']);
    Route::post('/register/profile', [RegistrationController::class, 'finish'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});
Route::middleware('auth')->group(function () {
    Route::get('/account/complete', [RegistrationController::class, 'create'])->name('account.complete');
    Route::post('/account/complete', [RegistrationController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/account/complete/verify', [RegistrationController::class, 'verifyPage']);
    Route::post('/account/complete/verify', [RegistrationController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('/account/complete/resend', [RegistrationController::class, 'resend'])->middleware('throttle:5,1');
    Route::get('/account/complete/profile', [RegistrationController::class, 'profile']);
    Route::post('/account/complete/profile', [RegistrationController::class, 'finish'])->middleware('throttle:10,1');
    Route::get('/teacher/student-invitations', [StudentInvitationController::class, 'create']);
    Route::post('/teacher/student-invitations', [StudentInvitationController::class, 'store'])->middleware('throttle:20,1');
    Route::get('/teacher/student-invitations/created', [StudentInvitationController::class, 'created']);
});
