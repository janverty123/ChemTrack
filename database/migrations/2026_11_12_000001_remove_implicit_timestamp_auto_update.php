<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * On MySQL 5.7 and older MariaDB with explicit_defaults_for_timestamp=OFF (common on shared hosting),
 * the first NOT NULL timestamp in a table silently receives ON UPDATE CURRENT_TIMESTAMP. That would
 * rewrite values such as attempts.started_at on every answer save. This removes the implicit
 * auto-update where it exists and leaves correctly created columns and SQLite untouched.
 */
return new class extends Migration
{
    /** @var array<int, array{0: string, 1: string}> */
    private array $columns = [
        ['attempts', 'started_at'],
        ['completions', 'completed_at'],
        ['student_invitations', 'expires_at'],
        ['reading_progress', 'saved_at'],
        ['cycle_releases', 'released_at'],
        ['signup_challenges', 'sent_at'],
        ['account_security_events', 'created_at'],
    ];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach ($this->columns as [$table, $column]) {
            // Alias the column: MySQL 8 reports information_schema column labels in upper case.
            $extra = DB::selectOne(
                'SELECT EXTRA AS column_extra FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [DB::getDatabaseName(), $table, $column],
            )?->column_extra;

            if ($extra !== null && str_contains(strtolower($extra), 'on update')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
            }
        }
    }

    public function down(): void
    {
        // Restoring an implicit auto-update would reintroduce the defect; nothing to reverse.
    }
};
