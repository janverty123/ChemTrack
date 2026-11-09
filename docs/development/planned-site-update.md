# Planned site update implementation plan

29 October 2026 (Asia/Manila). User requested implementation of the discussed plan.

**Goal:** make the agreed student identity, landing, dashboard and module-reading experience usable in the existing Laravel application, preserving old learner history.

**Architecture:** existing Laravel/MySQL/Blade monolith, existing server-side eligibility and immutable submissions. Extend activities with optional cycle grouping and reading metadata. Separate observed reading from confirmed activity completion. Use native browser motion and local fonts; PDF rendering is optional and loaded only for uploaded PDFs.

**Spec:** current decisions D019–D026; `docs/project/student-codes-and-form-verification.md`, `docs/design/dashboard-and-reader-progress.md`, `docs/design/pantas-landing-reference.md`, `docs/design/typography-and-theme.md`.

## Constraints and rulings

- One module, potentially composed of multiple ordered reading/checkpoint parts; do not manufacture another module. Teacher can assign parts to Cycle 1 or 2; old activities remain unassigned rather than receiving an invented research map.
- Existing demo history stays intact. Any new cycle example is explicitly synthetic, with no claim of approved test placement/materials.
- Student registration requests invitation, G/B category and password, never real name/email. Separate installation-wide counters issue ST1-G/ST1-B independently. Existing legacy accounts retain credentials/history; show a neutral reference until the teacher assigns a category/code.
- Native Google Forms responder UI remains selected. Link opening cannot confirm submission. No real Form/OAuth/material exists; expose accurate setup/verification-pending state and prevent external tests being completed through native lesson/assessment endpoints. Live response verification remains pending credentials/instruments.
- No score threshold, new retake rule, compulsory survey, or automatic PDF-to-HTML/OCR is introduced.
- Maintain the proposed coherent Manrope/Inter teal/off-white visual direction. Normal landing header scrolls away; separate fixed top-right menu. Reduced motion and usable narrow screens.
- Browser viewing is disabled in Work settings: do not bypass it. Use source, request, build and behavior checks; report visual/real-phone limits.
- No Git repository exists: use this workspace with scoped files and ignored local recovery material, without initializing Git or committing/pushing.
- Preserve the running password gate/tunnel, app and database, and keep secrets outside documentation/source.

## Tasks and contracts

### 1. Coded student identities and invitation registration

Owner: identity implementer. Files: User model, SessionController, new RegistrationController/service/invitation model and migration, teacher UserResource/form/table/pages, routes via separate `routes/registration.php`, registration/login views, focused feature tests. Parent alone updates main routes to require the new route file.

- [x] Add nullable student_code/category and nullable student email; locked G/B counters, unique codes and one-use hashed invitations scoped to a teacher/programme.
- [x] Tests fail first for separate sequences, code/password login, no name/email, invitation reuse/foreign teacher/role escalation, legacy login compatibility and teacher-owned roster code display.
- [x] Implement transaction-safe allocation and registration; teacher can create coded students and invitations, with neutral legacy references.
- [x] Run focused tests, record changes/evidence in ignored implementation report. Do not migrate local DB or edit shared learning/UI/CSS files.

Produces `User::studentReference(): string`, code login via `identifier` (legacy `email` requests still accepted), `routes/registration.php`, registration page. Never overwrite legacy names/emails as an incidental backfill.

### 2. Learning/reading foundation and teacher reporting

Owner: primary implementer. Files: Activity/ReadingProgress, migration, Learning service/controller, ReadingProgressController, ProgrammeForm, ReportController/results, main routes and tests.

- [x] Add nullable `cycle` (1/2), optional private `document_path`, `assessment_source` native/google_forms and optional google_form_url for pre/post tests. Native defaults preserve old data.
- [x] Save observational native/PDF position/version/pages through an enrolled, eligible student-only endpoint; reject stale versions and bound input. Never complete/unlock from reading writes. Programme freeze includes reading records.
- [x] Serve private PDFs only after the same eligibility checks. Keep files outside public storage.
- [x] Index provides `$cycle` plus `$states[activityId]` (`complete`, `locked`, `percent`, `detail`) computed over the WHOLE ordered sequence before cycle filtering, and `$reading` keyed by activity ID. Overall uses server-confirmed completed activity count, separate from reading/answer progress.
- [x] Activity view receives `$reading` nullable and `$version` content fingerprint. JSON reading POST contract: `version`, `position` (0–100), optional `furthest_position`, `current_page`, `page_count`, `viewed_pages` (bounded 1–500). Response includes `saved_at`, `furthest_position`, `position`, `current_page`, `page_count`, `viewed_pages`, `progress`; reading percentages are explicitly estimates.
- [x] All external-source assessments stay blocked from native scoring/completion. Form link alone cannot unlock. Live integration clearly pending.
- [x] Teacher completion matrix includes all assigned students, even no-work cases, codes, cycle, reading estimate, completion and score as separate values; CSV protects formula injection.
- [x] Test authorization, cycle/filter gate invariants, duplicate/monotonic/versioned reading saves, Form bypass denial, private PDF denial and native-history compatibility.

### 3. Student dashboard, reader and shared visual integration

Owner: student UI implementer. Files only: learning views, workspace component, `resources/css/workspace.css`, `resources/js/learning-ui.js`, `resources/js/reader.js`, relevant frontend test helper if needed. Parent owns app.css/app.js imports and package dependencies.

- [x] One large overall completion circle per programme, not a separate cycle circle. Cycle links select 1/2; small circle on every listed activity; percentage and status text accessible. Unassigned legacy map is labelled, not silently assigned.
- [x] Use backend states for locking regardless of filtering. Keep native save/retry forms intact; no answers or correct-option data exposed.
- [x] Reader uses bottom-centre oval outline, current stage/text and observational reading %, protected save/resume endpoint with explicit saving/saved/retry status. No scroll-only completion or invented intra-section gates.
- [x] Optional private PDF rendering uses PDF.js via dynamic import and bundled worker; render visible pages, track distinct viewed pages, save location/version and show retry/fallback on load failure. No OCR claim.
- [x] Manrope headings/Inter body supplied by parent; responsive safe-area bottom padding, keyboard focus and reduced motion. Existing teacher results remain readable.

### 4. Landing page and original motion

Owner: landing implementer. Files only: `resources/views/landing.blade.php`, `resources/css/landing.css`, `resources/js/landing.js`, task-specific feature test. Parent changes root route and imports.

- [x] Read agreed reference/type docs and Taste direction skill; preserve palette/type. Build original chemistry-remediation landing with real product explanation, honest demonstration labels, no fictional testimonials/efficacy statistics.
- [x] Normal header leaves viewport. Separate top-right fixed Menu button with accessible links, Escape/outside closure/focus restoration and safe-area spacing.
- [x] Scroll-driven/reveal product scenes explain one-module reading, submitted checkpoints and overall/cycle task progress. No scroll hijacking, no marketing animation changing actual learner records.
- [x] Root landing remains available to guests and signed-in users; role-aware dashboard link. Existing sign-in still `/login`.
- [x] Source/feature checks; record disabled browser inspection accurately.

### 5. Integration, rehearsal and handoff

- [x] Integrate owned routes/imports/font assets and optional PDF dependency; run all behavior tests, formatter, production build and relevant MySQL concurrency checks.
- [x] Create and verify an ignored local recovery dump and preserve baseline records/counts. Sequencing deviation: the dump was launched before additive migrations, but completion was not awaited until afterward; it was verified before creating separate synthetic preview content. This is not evidence of a rehearsed pre-migration restore.
- [x] Migrate additively; create clearly fictional unstarted preview content separately if needed, without changing frozen programmes.
- [x] Independently review the scoped tasks, fix/rereview material findings, and test public password-gated landing/login/reader paths. Whole-update reviewer reported no new material findings before a usage-limit interruption; final independent integration approval remains incomplete and is not claimed.
- [x] Update README/current context/session log/decisions/build evidence, linking actual implemented versus pending behavior. No production claim, no push or publication beyond the already-authorized temporary preview.

## Completion record — 30 October 2026

Implementation tasks are complete within the stated synthetic/configuration scope. See [evidence](site-update-evidence.md): 41 PHP tests/254 assertions, seven reader tests, formatting/build, MariaDB concurrency and local/public protected HTTP checks passed. Browser viewing is disabled, so new visual/PDF/real-device checks remain pending. Live Google verification, approved materials and the real cycle/assessment map remain outside completed work. No production deployment or Git operations.
