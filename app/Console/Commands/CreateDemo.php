<?php

namespace App\Console\Commands;

use App\Models\Programme;
use App\Models\User;
use App\Services\StudentAccounts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateDemo extends Command
{
    protected $signature = 'chemtrack:demo';

    protected $description = 'Create local-only fictional content and accounts; save generated sign-in details in .local/';

    public function handle(StudentAccounts $studentAccounts): int
    {
        if (! app()->environment('local')) {
            $this->error('Demo data is restricted to the local environment.');

            return self::FAILURE;
        }
        if (User::where('email', 'teacher@chemtrack.test')->exists()) {
            $this->info('Demo accounts already exist; no data or passwords changed. See .local/demo-accounts.json.');

            return self::SUCCESS;
        }

        $accounts = DB::transaction(function () use ($studentAccounts) {
            $teacherPassword = Str::password(16, symbols: false);
            $teacher = User::forceCreate([
                'name' => 'Ms Demo Teacher', 'email' => 'teacher@chemtrack.test', 'password' => $teacherPassword,
                'role' => 'teacher', 'email_verified_at' => now(), 'account_completed_at' => now(),
            ]);
            $accounts = [['role' => 'teacher', 'sign_in' => $teacher->email, 'password' => $teacherPassword]];

            $programme = Programme::forceCreate([
                'teacher_id' => $teacher->id, 'published' => true, 'cycle_workflow' => true,
                'title' => 'Atoms and ions',
                'description' => 'Fictional demonstration content. Each cycle has a pre-test, a module with a checkpoint, a video lesson and a post-test.',
            ]);
            $this->createCycle($programme, 1, 'Inside the atom', "Everything around you is made of matter. Atoms are the basic units that make up chemical elements.\n\nAn atom has a small, dense nucleus containing protons and neutrons. Protons carry a positive charge; neutrons have no electric charge. Electrons carry a negative charge and occupy regions around the nucleus.\n\nThe atomic number is the number of protons. Every carbon atom has six protons. Changing the number of protons changes the element.", [
                ['prompt' => 'Which subatomic particle carries a negative charge?', 'options' => ['Proton', 'Electron', 'Neutron'], 'correct' => 1],
                ['prompt' => 'What determines an element’s atomic number?', 'options' => ['Number of protons', 'Number of neutrons', 'Total number of particles'], 'correct' => 0],
            ]);
            $this->createCycle($programme, 2, 'From atoms to ions', "A neutral atom has equal numbers of protons and electrons, so their charges balance.\n\nAn ion forms when an atom gains or loses electrons. Losing a negatively charged electron produces a positive ion. Gaining an electron produces a negative ion.\n\nThe nucleus does not change during this process, so the element stays the same. Sodium, for example, can lose one electron to form a positive sodium ion.", [
                ['prompt' => 'When an atom gains an electron, what does it become?', 'options' => ['A positive ion', 'A different element', 'A negative ion'], 'correct' => 2],
                ['prompt' => 'What stays the same when an atom becomes an ion?', 'options' => ['The number of protons', 'The number of electrons', 'The overall charge'], 'correct' => 0],
            ]);

            foreach ([['G', 'Ana Demo', '100000000001'], ['B', 'Ben Demo', '100000000002']] as [$category, $fullName, $lrn]) {
                $password = Str::password(16, symbols: false);
                $student = $studentAccounts->create($teacher, $category, $password);
                $student->forceFill([
                    'email' => Str::lower($student->student_code).'@chemtrack.test', 'email_verified_at' => now(), 'account_completed_at' => now(),
                    'private_identity' => ['full_name' => $fullName, 'lrn' => $lrn, 'grade' => 'Grade 9', 'section' => 'Demo'],
                ])->save();
                $programme->students()->attach($student->id);
                $accounts[] = ['role' => 'student', 'sign_in' => $student->student_code, 'password' => $password];
            }

            return $accounts;
        });

        File::ensureDirectoryExists(base_path('.local'));
        File::put(base_path('.local/demo-accounts.json'), json_encode($accounts, JSON_PRETTY_PRINT).PHP_EOL);
        $this->info('Fictional demo created. Sign-in details saved in .local/demo-accounts.json (ignored by version control).');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{prompt: string, options: array<int, string>, correct: int}>  $questions
     */
    private function createCycle(Programme $programme, int $cycle, string $lessonTitle, string $lessonText, array $questions): void
    {
        $position = ($cycle - 1) * 5;
        $activities = [
            ['title' => "Cycle {$cycle} pre-test", 'type' => 'pre_test', 'questions' => $questions],
            ['title' => $lessonTitle, 'type' => 'lesson', 'content' => $lessonText."\n\nThis is demonstration material. Your teacher supplies the approved module."],
            ['title' => 'Check your understanding', 'type' => 'checkpoint', 'questions' => array_slice($questions, 0, 1)],
            ['title' => "Cycle {$cycle} video lesson", 'type' => 'video', 'video_url' => 'https://www.youtube.com/results?search_query='.urlencode($lessonTitle.' chemistry')],
            ['title' => "Cycle {$cycle} post-test", 'type' => 'post_test', 'questions' => $questions],
        ];
        foreach ($activities as $index => $activity) {
            $programme->activities()->create($activity + ['cycle' => $cycle, 'position' => $position + $index + 1, 'assessment_source' => 'native']);
        }
    }
}
