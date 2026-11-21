<?php

use App\Http\Controllers\CycleReleaseController;
use App\Http\Controllers\JoinProgrammeController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionController;
use App\Http\Middleware\EnsureCompletedAccount;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    return $user ? redirect($user->role === 'teacher' ? '/teacher' : '/learn') : view('landing');
})->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:login');
});
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth');
Route::get('/join', [JoinProgrammeController::class, 'show'])->middleware(EnsureCompletedAccount::class)->name('programmes.join');
Route::middleware('auth')->group(function () {
    Route::middleware(EnsureCompletedAccount::class)->group(function () {
        Route::post('/join', [JoinProgrammeController::class, 'store'])->middleware('throttle:20,1,join-programme');
        Route::get('/learn', [LearningController::class, 'index'])->name('learning.index');
        Route::get('/learn/activities/{activity}', [LearningController::class, 'show'])->name('learning.activity');
        Route::post('/learn/activities/{activity}/reading', [ReadingProgressController::class, 'store'])->middleware('throttle:120,1,reading');
        Route::get('/learn/activities/{activity}/document', [ReadingProgressController::class, 'document']);
        Route::post('/learn/activities/{activity}/complete', [LearningController::class, 'complete']);
        Route::post('/learn/activities/{activity}/save', [LearningController::class, 'save']);
        Route::post('/learn/activities/{activity}/submit', [LearningController::class, 'submit']);
    });
    Route::get('/teacher/programmes/{programme}/results', [ReportController::class, 'index']);
    Route::get('/teacher/programmes/{programme}/export', [ReportController::class, 'export']);
    Route::post('/teacher/programmes/{programme}/open-cycle-two', [CycleReleaseController::class, 'store'])->middleware('throttle:10,1,cycle-release');
});

require __DIR__.'/registration.php';
require __DIR__.'/settings.php';
