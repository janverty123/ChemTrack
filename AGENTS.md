# ChemTrack workspace instructions

## Read before work

Read `README.md`, `docs/context/current.md`, and the relevant project documents. Preserve confirmed decisions and distinguish planned work from implemented behavior. The source discussion is the chat titled “ChemTrack Recommendation”; its retrieved content has been summarized locally. Do not assume access to missing earlier messages.

## After every user prompt

Before the final response:
1. Refresh `README.md` with the current status, date (Asia/Manila), meaningful changes, and next step. For a question with no project change, keep its summary concise and do not invent progress.
2. Synchronize `docs/context/current.md` with decisions, blockers, and immediate next actions.
3. Append a short dated entry to `docs/context/session-log.md` recording the request, work or answer, checks, and unresolved items. Summarize; do not copy full conversations.
4. Record lasting architecture/scope changes in `docs/decisions/README.md`.

Never write credentials, connection strings with secrets, identifiable student records, or private research responses into context files. If the user prohibits file changes in a later request, honor that restriction and mention that context was not updated.

## Scope and organization

- Keep Laravel at the project root once scaffolded; retain its standard directory conventions.
- Keep documentation in `docs/`, source assets in `assets/`, and superseded material in `archive/`. Use descriptive lowercase hyphenated document names. Add new navigation links to the appropriate index.
- Use synthetic data in examples and tests. Keep identifiable research data outside this repository.
- A local development application now exists. See `docs/development/README.md` and the build evidence. Do not claim a feature, deployment, or test passed without evidence.
- Preserve existing user work and customized skills. Do not publish, purchase hosting, push, or deploy unless requested.

## Product and engineering

- Planned stack: Laravel monolith, MySQL, Blade/Tailwind with selective Livewire for students, Filament for teachers. Verify compatibility before pinning versions.
- Enforce access and cycle/assessment eligibility on the server; hiding UI controls is insufficient.
- Preserve attempt history, timestamps, and submitted answers. Score on the server and protect against duplicate submissions.
- Design for mobile data: explicit saving feedback, recoverable requests, confirmed saved answers, and resumable attempts. Do not promise offline support.
- Prioritize behavior tests for role boundaries, scoring, eligibility, retakes, answer persistence, reconnection, and duplicate submission handling.

## Skills

Skills are installed globally in the user's Codex skills directory. See `docs/skills/README.md` and `docs/skills/sources.json`. Do not copy global skill payloads into the project or modify upstream instructions for project preferences.

Use Universal Web Dev to route substantial web work. Hallmark provides an optional visual direction when relevant; Diagram Design is for standalone visual explanations. Choose one coherent approved design direction; do not combine multiple competing design manuals. Installing a skill does not require running it on unrelated tasks.

Use Universal Web Dev's `--sync` mode for updates, preserving modified/unmanaged skills. Never use its overwrite override without explicit user intent. Read the installed skill's current instructions when actually applying it. Optional PostgreSQL guidance does not change this project's MySQL decision.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `pnpm run build` or ask the user to run `pnpm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
