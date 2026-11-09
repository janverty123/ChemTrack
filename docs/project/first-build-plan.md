# First usable build implementation plan

> Execution: implement in the current folder, preserving existing documents. User requested development now; proceed with the established architecture and label unresolved study rules as demonstration defaults. No deployment, purchases, or source publication.

**Goal:** working local teacher-to-student remediation flow with authenticated roles, assigned programmes, sequential learning activities, saved answers, immutable submitted scores, and teacher results.

**Architecture:** Laravel 13 monolith; custom Blade/Tailwind student screens, Filament teacher panel, MySQL-compatible database. Use existing XAMPP PHP 8.3/MariaDB locally and SQLite for isolated automated tests. Do not alter existing XAMPP databases. Dependencies must resolve normally without bypassing platform/security requirements.

**Spec:** existing `product.md`, `architecture.md`, and `../diagrams/system-planning-overview.md`. Exact cycle count, pass thresholds, retakes, timers, and research instruments remain unconfirmed.

## First-build rulings

- Programme is the teacher-owned assignment container. Teachers configure ordered activities; activities may be lessons, pre-tests, checkpoints, or post-tests. No cycle count is hardcoded.
- Demonstration prerequisite is completion of the previous activity, independent of score. No timers, pass thresholds or retakes yet. One resumable attempt per student/activity; no submitted answer may be changed.
- Teachers enroll student accounts; no open self-registration or user-chosen teacher privileges. Demo accounts are local-only synthetic fixtures.
- Programmes with learner activity are frozen against content/question edits so research history stays interpretable. New versions can be created as separate programmes later.
- Multi-study billing, source-code sales, offline sync, email delivery and statistical hypothesis tests are outside this build.
- Visual proposal: quiet white/slate workspace, deep teal actions, blue-gray surfaces, system sans typography, a clear ordered learning path. No external fonts or trackers.

## Files and behavior

| Area | Files / responsibility |
| --- | --- |
| Framework | standard Laravel root files, Composer/npm lockfiles, `.env.example` |
| Accounts | `app/Models/User.php`, auth controller/middleware, login view, role checks |
| Learning data | Programme, Activity, Enrollment, Attempt, Completion models and migrations; JSON questions/answer history |
| Student flow | learning controller/service, student dashboard and activity views, server-side ownership/eligibility |
| Teacher flow | Filament Programme and Student resources, results page/export; teacher-scoped queries and policies |
| Demo | local-only seed command, clearly labeled fictional content and accounts |
| Verification | feature tests for auth, ownership, ordering, answer validation, resume, scoring and immutable duplicate submission |

## Tasks

### 1. Local framework and accounts
- [x] Scaffold Laravel into temporary staging and copy only non-conflicting files to this root; preserve docs, AGENTS, gitignore and tests README.
- [x] Install compatible Filament and frontend packages. Record resolved versions.
- [x] Write failing feature tests: guest redirects; wrong passwords denied; student cannot enter teacher panel; role cannot be promoted by request input; teacher login allowed.
- [x] Implement session login/logout, role-restricted student routes, teacher panel permissions.
- [x] Run tests to green; no credentials in documentation/logs.

### 2. Assigned learning flow
- [x] Write failing tests: non-enrolled/foreign teacher denied, later activity locked, lesson completion unlocks next, answers resume from database, invalid option rejected, correct score computed, duplicate submit preserves original result.
- [x] Implement schema, relations, scoped access checks, and transactional locking for saves/submission/completion. Hide question keys from student payloads.
- [x] Add authored synthetic demo programme and accounts using generated local credentials stored only in ignored local file.
- [x] Run full feature suite, including frozen programme edits and teacher scope.

### 3. Usable student/teacher screens
- [x] Build login, student learning path, lesson view, assessment save/resume/submit/results, with visible pending/error feedback and mobile/keyboard support.
- [x] Build teacher programme/ordered-activity editing, enrollment and student management, progress/results and scoped CSV export.
- [x] Verify real HTTP login and core navigation; compile production assets and inspect desktop/phone screenshots if browser tools available.

### 4. Handoff
- [x] Run whole test suite, formatter check, production asset build, route/migration checks and local database smoke test.
- [x] Update README, development instructions, context, decision log, roadmap and task status with actual evidence and limits.
- [x] Leave a local-only preview and clear instructions to reopen it; no deployment or public binding.

## Review focus

1. Students attempting to access another student's attempt or an unenrolled programme must be denied.
2. Concurrent or repeated saves/submissions must never overwrite a submitted attempt or create duplicate final results.
3. Invalid/unanswered questions must be rejected where submission requires completeness; legitimate zero scores are retained.
4. Teacher queries, relationship selectors and exports must stay within that teacher's scope.
5. Browser disconnects must show an unconfirmed state; only acknowledged persisted answers count as saved.

No completion claim until commands/flows actually pass. SQLite results do not establish MySQL concurrency behavior; report integration coverage separately.

## Completed evidence

First usable local build completed on 28 October 2026; user review and real research policy/content confirmation remain pending. Final suite: 17 tests / 72 assertions, formatting pass, production build pass, Composer validation/audit pass. Chrome/MariaDB full demo flow and save recovery/export verified. Independent review found three races, reproduced with failing tests and corrected; two-connection MariaDB regression now passes. See `../development/first-build-evidence.md`. No Git commits, public hosting, purchases, or deployment.
