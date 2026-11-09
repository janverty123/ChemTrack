<?php

use App\Http\Controllers\CycleReleaseController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionController;
use App\Http\Middleware\EnsureCompletedAccount;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing'))->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:5,1');
});
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth');
Route::middleware('auth')->group(function () {
    Route::middleware(EnsureCompletedAccount::class)->group(function () {
        Route::get('/learn', [LearningController::class, 'index'])->name('learning.index');
        Route::get('/learn/activities/{activity}', [LearningController::class, 'show'])->name('learning.activity');
        Route::post('/learn/activities/{activity}/reading', [ReadingProgressController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/learn/activities/{activity}/document', [ReadingProgressController::class, 'document']);
        Route::post('/learn/activities/{activity}/complete', [LearningController::class, 'complete']);
        Route::post('/learn/activities/{activity}/save', [LearningController::class, 'save']);
        Route::post('/learn/activities/{activity}/submit', [LearningController::class, 'submit']);
    });
    Route::get('/teacher/programmes/{programme}/results', [ReportController::class, 'index']);
    Route::get('/teacher/programmes/{programme}/export', [ReportController::class, 'export']);
    Route::post('/teacher/programmes/{programme}/open-cycle-two', [CycleReleaseController::class, 'store'])->middleware('throttle:10,1');
});

require __DIR__.'/registration.php';
require __DIR__.'/settings.php';
