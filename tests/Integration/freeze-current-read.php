<?php

use App\Models\Programme;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! app()->environment('local') || config('database.default') !== 'mysql' || ! str_ends_with(config('database.connections.mysql.database'), '_development')) {
    exit(2);
}

$teacher = User::factory()->create(['role' => 'teacher']);
$student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
$programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Temporary concurrency check']);
$activity = $programme->activities()->create(['title' => 'Concurrency fixture', 'type' => 'checkpoint', 'position' => 1, 'questions' => [['prompt' => 'Choose', 'options' => ['A', 'B'], 'correct' => 0]]]);
config(['database.connections.freeze_writer' => config('database.connections.mysql')]);
$writer = DB::connection('freeze_writer');
$caught = false;
try {
    DB::beginTransaction();
    DB::table('attempts')->where('activity_id', $activity->id)->exists();
    $writer->transaction(function () use ($writer, $programme, $activity, $student) {
        $writer->table('programmes')->where('id', $programme->id)->lockForUpdate()->first();
        $writer->table('attempts')->insert(['user_id' => $student->id, 'activity_id' => $activity->id, 'answers' => '[]', 'started_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    });
    try {
        $programme->ensureEditable();
    } catch (ValidationException $e) {
        $caught = true;
    }
} finally {
    if (DB::transactionLevel()) {
        DB::rollBack();
    }
    DB::table('attempts')->where('activity_id', $activity->id)->delete();
    DB::table('activities')->where('id', $activity->id)->delete();
    DB::table('programmes')->where('id', $programme->id)->delete();
    DB::table('users')->where('id', $student->id)->delete();
    DB::table('users')->where('id', $teacher->id)->delete();
    DB::purge('freeze_writer');
}
echo $caught ? 'PASS: content freeze sees newly committed learner activity despite earlier transaction snapshot.'.PHP_EOL : 'FAIL: content freeze used stale transaction snapshot.'.PHP_EOL;
exit($caught ? 0 : 1);
