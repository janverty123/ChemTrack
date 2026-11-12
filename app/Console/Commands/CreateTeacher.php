<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateTeacher extends Command
{
    /**
     * @var string
     */
    protected $signature = 'chemtrack:create-teacher {email? : Teacher sign-in email} {--name= : Display name shown in the workspace}';

    /**
     * @var string
     */
    protected $description = 'Create a teacher account (teacher accounts are provisioned by the maintainer, not self-registered)';

    public function handle(): int
    {
        $email = Str::lower(trim((string) ($this->argument('email') ?? $this->ask('Teacher email'))));
        $name = trim((string) ($this->option('name') ?? $this->ask('Display name')));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
                'name' => ['required', 'string', 'max:100'],
                'password' => ['required', 'confirmed', 'max:200', Password::min(12)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $teacher = User::forceCreate([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'teacher',
            'email_verified_at' => now(),
            'account_completed_at' => now(),
        ]);

        $this->info("Teacher account created for {$teacher->email}. Sign in at ".rtrim((string) config('app.url'), '/').'/login');

        return self::SUCCESS;
    }
}
