<?php

// Standalone MariaDB rehearsal; all writes use disposable prefixed tables.
use App\Models\Programme;
use App\Models\StudentInvitation;
use App\Models\User;
use App\Services\StudentAccounts;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function isolatedConnection(string $prefix): void
{
    check((bool) preg_match('/\Achemtrack_race_[a-f0-9]{12}_\z/', $prefix), 'Unsafe rehearsal table prefix.');
    $config = DB::connection()->getConfig();
    check(app()->environment('local') && $config['driver'] === 'mysql'
        && in_array($config['host'], ['127.0.0.1', 'localhost', '::1'], true)
        && $config['database'] === 'chemtrack_development' && $config['prefix'] === ''
        && empty($config['unix_socket']), 'Refusing a non-local/non-development database.');
    DB::connection()->beforeExecuting(function (string $sql): void {
        check((bool) preg_match('/\A\s*(?:select|show)\b/i', $sql), 'Write to the real application connection blocked.');
    });
    // Resolved configs contain the old name; hydrated Eloquent models retain it.
    $config['name'] = 'registration_race';
    $config['prefix'] = $prefix;
    $config['url'] = null;
    config(['database.connections.registration_race' => $config]);
    DB::setDefaultConnection('registration_race');
    DB::connection()->beforeExecuting(function (string $sql): void {
        check(! preg_match('/`(?:users|programmes|student_code_counters|student_invitations|programme_user)`/', $sql), 'Unprefixed application-table query blocked.');
    });
    foreach ([User::class, Programme::class, StudentInvitation::class] as $model) {
        $connection = (new $model)->getConnection();
        check($connection->getTablePrefix() === $prefix && $connection->getName() === 'registration_race', 'An Eloquent model escaped the isolated connection.');
    }
    DB::statement('SET SESSION innodb_lock_wait_timeout = 15');
}

$inputDirectory = dirname(__DIR__, 2).'/.local/site-update';
if (($argv[1] ?? '') === '--child') {
    try {
        $path = realpath($argv[2] ?? '');
        check($path !== false && dirname($path) === realpath($inputDirectory)
            && preg_match('/\Acoded-registration-[a-f0-9]{24}\.json\z/', basename($path)), 'Unsafe child input path.');
        $input = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        isolatedConnection($input['prefix']);
        $target = $input['target'] === 'counter' ? 'student_code_counters' : 'student_invitations';
        $announced = false;
        DB::connection()->beforeExecuting(function (string $sql) use ($target, &$announced): void {
            if (! $announced && str_starts_with(strtolower($sql), 'select') && str_contains($sql, $target)) {
                $announced = true;
                echo 'READY'.PHP_EOL;
                flush();
            }
        });
        try {
            $student = app(StudentAccounts::class)->register($input['token'], $input['category'], $input['password']);
            $result = ['status' => 'created', 'id' => $student->id, 'code' => $student->student_code];
        } catch (ValidationException $exception) {
            $result = ['status' => 'rejected', 'fields' => array_keys($exception->errors())];
        }
        echo 'RESULT '.json_encode($result, JSON_THROW_ON_ERROR).PHP_EOL;
        exit(0);
    } catch (Throwable $exception) {
        // Do not print SQL, bindings, tokens or connection configuration.
        fwrite(STDERR, 'Child failed: '.get_class($exception).PHP_EOL);
        if ($exception instanceof QueryException) {
            fwrite(STDERR, 'Database driver error: '.($exception->errorInfo[1] ?? 'unknown').PHP_EOL);
            if (preg_match('/for key \'([^\']+)\'/', $exception->errorInfo[2] ?? '', $match)) {
                fwrite(STDERR, 'Constraint: '.$match[1].PHP_EOL);
            }
        }
        exit(1);
    }
}

$prefix = 'chemtrack_race_'.bin2hex(random_bytes(6)).'_';
$tableNames = ['programme_user', 'student_invitations', 'student_code_counters', 'programmes', 'users'];
$files = [];
$processes = [];
$real = DB::connection();
$snapshot = null;
$ownsTables = false;
$exitCode = 1;

function realSnapshot($connection): array
{
    $snapshot = [];
    foreach ($connection->getSchemaBuilder()->getTableListing() as $table) {
        // This connection has no prefix and is read-only throughout the rehearsal.
        $snapshot[$table] = $connection->table($table)->count();
    }
    ksort($snapshot);
    $snapshot['counter_digest'] = hash('sha256', json_encode($connection->table('student_code_counters')->orderBy('category')->get()));
    $snapshot['used_invites'] = $connection->table('student_invitations')->whereNotNull('used_at')->count();

    return $snapshot;
}

try {
    isolatedConnection($prefix);
    check(str_contains(strtolower(DB::selectOne('SELECT VERSION() AS version')->version), 'mariadb'), 'This rehearsal requires MariaDB.');
    $snapshot = realSnapshot($real);
    $schema = Schema::connection('registration_race');
    foreach ($tableNames as $table) {
        check(! $schema->hasTable($table), 'Rehearsal prefix already exists.');
    }
    $ownsTables = true;
    $schema->create('users', function (Blueprint $table): void {
        $table->engine = 'InnoDB';
        $table->id();
        $table->string('name');
        $table->string('email')->nullable()->unique();
        $table->string('password');
        $table->string('role');
        $table->unsignedBigInteger('teacher_id')->nullable();
        $table->string('student_code', 40)->nullable()->unique();
        $table->char('category', 1)->nullable();
        $table->timestamps();
    });
    $schema->create('programmes', function (Blueprint $table): void {
        $table->engine = 'InnoDB';
        $table->id();
        $table->unsignedBigInteger('teacher_id');
        $table->string('title');
        $table->timestamps();
    });
    $schema->create('student_code_counters', function (Blueprint $table): void {
        $table->engine = 'InnoDB';
        $table->char('category', 1)->primary();
        $table->unsignedBigInteger('last_number')->default(0);
    });
    $schema->create('student_invitations', function (Blueprint $table): void {
        $table->engine = 'InnoDB';
        $table->id();
        $table->unsignedBigInteger('teacher_id');
        $table->unsignedBigInteger('programme_id');
        $table->string('token_hash', 64)->unique('race_token_unique');
        $table->timestamp('expires_at');
        $table->timestamp('used_at')->nullable();
        $table->unsignedBigInteger('used_by')->nullable();
        $table->timestamps();
    });
    $schema->create('programme_user', function (Blueprint $table): void {
        $table->engine = 'InnoDB';
        $table->unsignedBigInteger('programme_id');
        $table->unsignedBigInteger('user_id');
        $table->unique(['programme_id', 'user_id'], 'race_enrollment_unique');
        $table->timestamps();
    });
    DB::table('student_code_counters')->insert([
        ['category' => 'G', 'last_number' => 0], ['category' => 'B', 'last_number' => 0],
    ]);
    $teacher = DB::table('users')->insertGetId(['name' => 'Synthetic race teacher', 'role' => 'teacher', 'password' => 'unusable-fixture']);
    $programmes = [];
    foreach ([1, 2, 3] as $number) {
        $programmes[] = DB::table('programmes')->insertGetId(['teacher_id' => $teacher, 'title' => 'Synthetic race '.$number]);
    }
    $makeInvitation = function (int $programme) use ($teacher): string {
        $token = bin2hex(random_bytes(32));
        DB::table('student_invitations')->insert([
            'teacher_id' => $teacher, 'programme_id' => $programme,
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->addHour(),
        ]);

        return $token;
    };
    $runPair = function (array $tokens, string $category, string $target) use ($prefix, $inputDirectory, &$files, &$processes): array {
        $pair = [];
        DB::beginTransaction();
        if ($target === 'counter') {
            DB::table('student_code_counters')->where('category', $category)->lockForUpdate()->first();
        } else {
            DB::table('student_invitations')->where('token_hash', hash('sha256', $tokens[0]))->lockForUpdate()->first();
        }
        foreach ($tokens as $token) {
            $path = $inputDirectory.'/coded-registration-'.bin2hex(random_bytes(12)).'.json';
            $files[] = $path;
            check(file_put_contents($path, json_encode([
                'prefix' => $prefix, 'token' => $token, 'password' => bin2hex(random_bytes(24)),
                'category' => $category, 'target' => $target,
            ], JSON_THROW_ON_ERROR)) !== false, 'Cannot write ignored child input.');
            $process = new Process([PHP_BINARY, __FILE__, '--child', $path], dirname(__DIR__, 2));
            $process->setTimeout(60);
            $process->start();
            $processes[] = $process;
            $pair[] = $process;
        }
        $deadline = microtime(true) + 20;
        do {
            $ready = true;
            foreach ($pair as $process) {
                $process->checkTimeout();
                check($process->isRunning(), 'Child exited before concurrent lock contention.');
                $ready = $ready && str_contains($process->getOutput(), 'READY');
            }
            if ($ready) {
                break;
            }
            usleep(10000);
        } while (microtime(true) < $deadline);
        check($ready, 'Children did not reach the controlled lock barrier.');
        // Both real register calls have reached the locked row before either can finish.
        DB::commit();
        $results = [];
        foreach ($pair as $process) {
            if ($process->wait() !== 0 || $process->getErrorOutput() !== '') {
                // Child diagnostics intentionally contain class/error numbers only.
                fwrite(STDERR, $process->getErrorOutput());
                throw new RuntimeException('A registration child failed.');
            }
            check((bool) preg_match('/^RESULT (.+)$/m', $process->getOutput(), $match), 'Missing registration result.');
            $results[] = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        }

        return $results;
    };

    // Distinct programmes prevent their shared programme row from serializing the counter test.
    $distinctTokens = [$makeInvitation($programmes[0]), $makeInvitation($programmes[1])];
    $results = $runPair($distinctTokens, 'G', 'counter');
    $codes = array_column($results, 'code');
    sort($codes);
    check(array_column($results, 'status') === ['created', 'created'] && $codes === ['ST1-G', 'ST2-G'], 'Distinct concurrent invites must issue ST1-G and ST2-G.');
    check(DB::table('users')->where('role', 'student')->count() === 2
        && DB::table('programme_user')->count() === 2
        && DB::table('student_invitations')->whereNotNull('used_at')->whereNotNull('used_by')->count() === 2
        && DB::table('student_code_counters')->where('category', 'G')->value('last_number') === 2, 'Distinct invite side effects must commit exactly once.');
    echo 'PASS: concurrent distinct invitations assigned ST1-G and ST2-G with two accounts/enrollments/consumptions.'.PHP_EOL;

    $sameToken = $makeInvitation($programmes[2]);
    $results = $runPair([$sameToken, $sameToken], 'B', 'invitation');
    $statuses = array_column($results, 'status');
    sort($statuses);
    check($statuses === ['created', 'rejected'], 'Concurrent reuse must create one account and reject one request.');
    $created = array_values(array_filter($results, fn (array $result) => $result['status'] === 'created'))[0];
    $rejected = array_values(array_filter($results, fn (array $result) => $result['status'] === 'rejected'))[0];
    check($created['code'] === 'ST1-B' && $rejected['fields'] === ['invitation'], 'Replay must be an invitation validation failure without allocating another code.');
    check(DB::table('users')->where('role', 'student')->count() === 3
        && DB::table('programme_user')->count() === 3
        && DB::table('student_invitations')->whereNotNull('used_at')->count() === 3
        && DB::table('student_invitations')->where('token_hash', hash('sha256', $sameToken))->value('used_by') === $created['id']
        && DB::table('student_code_counters')->where('category', 'B')->value('last_number') === 1
        && DB::table('student_code_counters')->where('category', 'G')->value('last_number') === 2, 'Replay must not create another account, enrollment, consumption or counter increment.');
    echo 'PASS: concurrent same invitation created ST1-B once; replay raised invitation validation; B counter remains 1.'.PHP_EOL;
    $exitCode = 0;
} catch (Throwable $exception) {
    $message = get_class($exception) === RuntimeException::class ? $exception->getMessage() : get_class($exception);
    fwrite(STDERR, 'FAIL: '.$message.PHP_EOL);
    if ($exception instanceof QueryException) {
        fwrite(STDERR, 'Database driver error: '.($exception->errorInfo[1] ?? 'unknown').PHP_EOL);
    }
} finally {
    // Stop children before cleanup, including failure/timeout paths.
    foreach ($processes as $process) {
        if ($process->isRunning()) {
            $process->stop(1);
        }
    }
    try {
        if ($ownsTables) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            check((bool) preg_match('/\Achemtrack_race_[a-f0-9]{12}_\z/', $prefix), 'Unsafe cleanup prefix.');
            foreach ($tableNames as $table) {
                Schema::connection('registration_race')->dropIfExists($table);
            }
            foreach ($tableNames as $table) {
                check(! Schema::connection('registration_race')->hasTable($table), 'Prefixed table cleanup failed.');
            }
            echo 'PASS: only five allowlisted prefixed synthetic tables created and removed.'.PHP_EOL;
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, 'FAIL: prefixed table cleanup failed; inspect isolated rehearsal tables.'.PHP_EOL);
        $exitCode = 1;
    }
    foreach ($files as $file) {
        check(dirname($file) === $inputDirectory
            && preg_match('/\Acoded-registration-[a-f0-9]{24}\.json\z/', basename($file)), 'Unsafe input cleanup path.');
        if (is_file($file)) {
            check(unlink($file), 'Ignored child input cleanup failed.');
        }
    }
    if ($snapshot !== null) {
        try {
            check(realSnapshot($real) === $snapshot, 'Real application snapshot changed.');
            echo 'PASS: real application table counts, counter values and used-invitation count unchanged.'.PHP_EOL;
        } catch (Throwable $exception) {
            fwrite(STDERR, 'FAIL: real application table counts/counter digest/invitation consumption changed or could not be verified.'.PHP_EOL);
            $exitCode = 1;
        }
    }
}
exit($exitCode);
