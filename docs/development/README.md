# Development guide

Latest 4 November refinement: [auth/theme evidence](auth-theme-refinement.md). Public/new-account light defaults, shared floating-label account entry and in-field password controls implemented; full96tests697assertions, frontend33, Pint/build and liveHTTPchecks passed. Visual browser checks remain blocked by Work settings.

## Latest cycle/account/settings build

See [implementation evidence](cycle-accounts-settings-evidence.md) and [mail/account setup](mail-and-account-readiness.md). 4 November: independent cycle totals, teacher release, staged verified signup/private identities and profile/security/theme settings implemented. Full93tests640assertions, frontend30, Pint/build and liveHTTPchecks passed. Browser viewing currently disabled; no fresh visual/mobile evidence. Three additive local migrations applied after backup; existing core record counts/credentials/history unchanged. Local site8002, remote tunnel stopped. Real sender/Form connection/materials remain pending.

## Latest presentation pass

See [presentation review](presentation-polish.md): shared teacher/student circles, cycle filtering and concise presentation copy. Final checks on 3 November: 53 tests/319 assertions, 23 frontend checks, Pint and Vite build passed. Desktop browser paths checked; current responsive override unavailable. Local server restarted at port 8002; remote preview is stopped.

## Current setup

Working local Laravel application. PHP 8.3.36 and MariaDB 10.11.16 come from XAMPP. The local PHP configuration in `%LOCALAPPDATA%/ChemTrack/tools/php.ini` enables intl/zip without modifying XAMPP's global configuration. Composer is installed in that same user-local tools folder. Node 24.22.1 and bundled pnpm were used for frontend assets.

## Running on this computer

See [step-by-step opening instructions](open-local-site.md) for XAMPP, the launcher, sign-in details and troubleshooting. On 29 October the website server was stopped while MySQL was running; restarting restored HTTP 200 and browser-verified student/teacher sign-in. This was a startup issue, not a database reset or code fix.

1. Start MySQL from XAMPP if it is not running.
2. Open `http://127.0.0.1:8000/login` if the preview is running; otherwise double-click `Start-ChemTrack.cmd` or run `./scripts/dev.ps1 serve` in PowerShell.
3. Open the ignored `.local/demo-accounts.json` locally for generated demo sign-in details. Never publish this file. Alex's programme is untouched; Sam completed the demonstration in browser verification.

The server binds to loopback only. It is a development preview, not a production server. The existing local database uses its own scoped database account in ignored `.env`; no existing databases were reset.

The current temporary phone preview uses a separate loopback Caddy password gate and Cloudflare HTTPS transport. Use its new HTTPS link while secure-cookie preview mode is active; startup, checks and shutdown are in the [remote preview guide](remote-preview.md). Keep preview credentials in ignored local settings. The old email-PIN link is retired.

## Commands

Run from the project root in PowerShell:

```powershell
./scripts/dev.ps1 test
./scripts/dev.ps1 integration
./scripts/dev.ps1 build
./scripts/dev.ps1 format
./scripts/dev.ps1 routes
node --test tests/frontend/reader.test.mjs
node --test tests/frontend/landing-motion.test.mjs tests/frontend/landing-interactions.test.mjs
node --test tests/frontend/workspace-navigation.test.mjs
```

`test` runs isolated SQLite feature tests. `integration` runs the two-connection MariaDB content-freeze regression, creating and cleaning only its own temporary synthetic records; it refuses a non-local/non-development database. `format` checks formatting without editing. `migrate` applies outstanding migrations; `demo` creates fictional local data once and preserves existing accounts/passwords on repeat runs.

The [current site-update evidence](site-update-evidence.md) covers coded registration, rings/cycles, native/private PDF reading, landing and reports. New coded sample credentials are in ignored `.local/site-update/updated-preview.json`; original credentials remain valid. All content is fictional. Run the separate real-MariaDB registration rehearsal with the user-local PHP configuration:

```powershell
$env:PHPRC = Join-Path $env:LOCALAPPDATA 'ChemTrack/tools/php.ini'
& 'C:/xampp/php/php.exe' tests/Integration/coded-registration-race.php
```

It invokes two real registration processes against disposable, prefix-isolated tables and verifies original counts/counters/invite usage stay unchanged. It refuses non-local/non-development databases. This is a controlled concurrency regression, not a load test. PDF rendering and new UI visual checks remain pending while browser viewing is disabled.

## Setup on another computer

Install PHP 8.3+ with required Laravel extensions plus intl/zip, Composer, Node and pnpm/npm, and a compatible MySQL/MariaDB server. Run `composer install` and `pnpm install --frozen-lockfile` (or establish an npm lockfile if choosing npm). Copy `.env.example` to `.env`, supply a dedicated database/user, generate an application key with `php artisan key:generate`, then run `php artisan migrate`, `php artisan chemtrack:demo`, and `pnpm run build`. Use `php artisan serve --host=127.0.0.1` for a local preview. Fresh-machine setup is documented but has not been tested on a second machine.

Dependency assets can be republished with `php artisan filament:assets`; they are generated and ignored in version control. Real hosting requirements, TLS, secure cookies, backups/restore, student-data retention and deployment remain to be verified before any live use.

## Verification priorities

The [teacher workspace update](teacher-workspace-evidence.md) adds a separate Vite theme input, actionable overview, student profiles and read-only programme views. Build assets after theme edits. Existing teacher/account/upload tests plus TeacherWorkspaceTest cover ownership, profile privacy, frozen content, enrolment and saved-question retention. No database migration is required for this update.

See [first-build evidence](first-build-evidence.md). Continue to test permissions, ownership isolation, scoring, prerequisites, answer persistence, connection failures, and duplicate submissions. Retakes are currently disabled, not implemented as an override flow. Check real phones, keyboard navigation, contrast, and save/error feedback before a live study.

## File conventions

Use Laravel's standard root directories. Put design decisions/mockups in `docs/design/`, diagrams in `docs/diagrams/`, blank research instruments in `docs/research/`, and source assets in `assets/`. When the app exists, place browser-served resources in its standard `resources/` or `public/` directories. Never expose internal research files through `public/`.
