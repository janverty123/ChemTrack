# First build evidence

28 October 2026, Asia/Manila. Local development version; no deployment or research validation.

## Environment

- XAMPP PHP 8.3.36; user-local PHP configuration enables existing intl/zip extensions without changing XAMPP's global configuration.
- Laravel 13.39.0, Filament 5.11.0, Livewire 4.4.8, Tailwind 4.3.3, Vite 8.3.5. Composer and pnpm lockfiles record exact resolution.
- MariaDB 10.11.16 locally via Laravel's MySQL driver; separate `chemtrack_development` database and generated restricted database account. SQLite in-memory feature tests isolate test data from local records.
- Composer 2.10.3 installed in user-local ChemTrack tools after installer signature verification. No administrator install or global PATH/config change. Existing local MariaDB started bound to loopback.

## Checks

- Feature tests were run before implementation and failed for missing routes/roles/models; implemented features subsequently passed.
- Final complete suite: 17 tests / 72 assertions passed. PHP formatting check passed; Composer manifest validated and dependency audit reported no known advisories; all four migrations applied to the local MariaDB database.
- Feature coverage: login/logout and throttling, teacher/student access, enrollment and sequential prerequisites, saved answers/resume, validation, scores including zero, duplicate submission, post-submission save rejection, unpublished content, frozen programme content, teacher scope and CSV response.
- Filament integration checks: creating ordered lesson/assessment content and editing a student's profile without exposing/replacing their password.
- Production frontend build succeeded; PHP formatting applied, with final check recorded in session handoff.
- Real Chrome headless browser against MariaDB: login, student/teacher pages and create forms; desktop and emulated 390px screenshots; student dashboard had no horizontal overflow.
- Full browser learning flow: failed save induced, retry confirmed, page reload restored the answer, pre-test scored 67%, lessons completed, checkpoint and post-test scored 100%, all five activities completed. Teacher report displayed results and CSV downloaded without names/emails. No browser script errors in these flows.
- Independent code review identified a MariaDB transaction-snapshot content-freeze race, stale displayed questions on attempt start, and a stale lesson-type completion bypass. Each was reproduced before correction. Two feature regressions and `tests/Integration/freeze-current-read.php` now pass. The database regression uses two connections with one committing learner activity after the other's initial snapshot.

These checks use fictional content/accounts. They do not establish concurrent classroom capacity, production hosting compatibility, full accessibility, real-phone behavior, statistical validity, or a comprehensive security audit. Retakes, pass thresholds, timers, two-cycle research rules, participant consent/retention and real learning material still require confirmation.

Browser smoke scripts and screenshots are local-only in `.local/` because they use generated local sign-in details. Repeatable framework tests are in `tests/Feature/`.

Official compatibility references checked before installation: [Laravel 13 release notes](https://laravel.com/framework/docs/13.x/releases) and [Filament 5 installation](https://filamentphp.com/docs/5.x/introduction/installation). Actual package versions above come from resolved lockfiles/runtime, rather than assumptions from the earlier chat.
