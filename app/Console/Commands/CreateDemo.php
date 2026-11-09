<?php

namespace App\Console\Commands;

use App\Models\Programme;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateDemo extends Command
{
    protected $signature = 'chemtrack:demo';

    protected $description = 'Create local-only fictional content and accounts; save generated sign-in details in .local/';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Demo data is restricted to the local environment.');

            return self::FAILURE;
        }
        if (User::whereIn('email', ['teacher@chemtrack.test', 'student@chemtrack.test', 'learner@chemtrack.test'])->exists()) {
            $this->info('Demo accounts already exist; no data or passwords changed. See .local/demo-accounts.json.');

            return self::SUCCESS;
        }
        $accounts = DB::transaction(function () {
            $accounts = [];
            foreach ([['Demo Teacher', 'teacher@chemtrack.test', 'teacher'], ['Alex Demo', 'student@chemtrack.test', 'student'], ['Sam Demo', 'learner@chemtrack.test', 'student']] as [$name, $email, $role]) {
                $password = Str::password(22, symbols: false);
                $user = User::create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role, 'teacher_id' => $role === 'student' ? $teacher->id : null]);
                if ($role === 'teacher') {
                    $teacher = $user;
                }
                $accounts[] = compact('name', 'email', 'role', 'password');
            }
            $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Foundations of chemistry · Demo', 'description' => 'A short demonstration of pre-test, lessons, checkpoints and post-test. These sample questions are not validated research instruments.', 'published' => true]);
            $questions = [
                ['prompt' => 'Which subatomic particle carries a negative charge?', 'options' => ['Proton', 'Electron', 'Neutron'], 'correct' => 1],
                ['prompt' => 'What determines an element’s atomic number?', 'options' => ['Number of protons', 'Number of neutrons', 'Total number of particles'], 'correct' => 0],
                ['prompt' => 'When an atom gains an electron, what does it become?', 'options' => ['A positive ion', 'A different element', 'A negative ion'], 'correct' => 2],
            ];
            $programme->activities()->create(['title' => 'Before you begin', 'type' => 'pre_test', 'position' => 1, 'questions' => $questions]);
            $programme->activities()->create(['title' => 'Inside the atom', 'type' => 'lesson', 'position' => 2, 'content' => "Everything around you is made of matter. Atoms are the basic units that make up chemical elements.\n\nAn atom has a small, dense nucleus containing protons and neutrons. Protons carry a positive charge; neutrons have no electric charge. Electrons carry a negative charge and occupy regions around the nucleus.\n\nThe atomic number is the number of protons. Every carbon atom has six protons. Changing the number of neutrons produces an isotope; changing the number of protons changes the element.\n\nCheck your understanding: An atom has eight protons. Its atomic number is eight, regardless of how many neutrons it has.\n\nThis is demonstration learning material. Your teacher will supply the approved research modules."]);
            $programme->activities()->create(['title' => 'Check your understanding', 'type' => 'checkpoint', 'position' => 3, 'questions' => array_slice($questions, 0, 2)]);
            $programme->activities()->create(['title' => 'From atoms to ions', 'type' => 'lesson', 'position' => 4, 'content' => "A neutral atom has equal numbers of protons and electrons. Their opposite charges balance.\n\nAn ion forms when an atom gains or loses electrons. Losing a negatively charged electron produces a positive ion. Gaining an electron produces a negative ion.\n\nThe nucleus does not change during this process: the element remains the same. For example, sodium can lose one electron to form a positive sodium ion.\n\nPause and explain in your own words why gaining a negative electron makes an atom negatively charged.\n\nComplete the final sample assessment when you are ready."]);
            $programme->activities()->create(['title' => 'See what you have learned', 'type' => 'post_test', 'position' => 5, 'questions' => $questions]);
            $programme->students()->attach(User::where('teacher_id', $teacher->id)->pluck('id'));

            return $accounts;
        });
        File::ensureDirectoryExists(base_path('.local'));
        File::put(base_path('.local/demo-accounts.json'), json_encode($accounts, JSON_PRETTY_PRINT).PHP_EOL);
        $this->info('Fictional demo created. Generated sign-in details saved in .local/demo-accounts.json (ignored by version control).');

        return self::SUCCESS;
    }
}
