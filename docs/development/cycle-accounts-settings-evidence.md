# Cycle progress, verified accounts and settings — implementation evidence

4 November 2026 (Asia/Manila). The user authorized execution of the [discussion plan](../project/cycle-progress-accounts-settings-plan.md). Implemented locally; no publication or production deployment.

## Result

- One circle switches between independent Cycle 1/2 totals and matching right-side tasks in student and teacher student-profile views. Four required stages each contribute 25%: pre-test, module (including every embedded checkpoint), standalone video and post-test. Missing setup never increases completion. Reports and CSV contain separate cycle completion plus explicit stage-summary rows, including unavailable stages.
- Cycle 2 requires completed Cycle 1 AND teacher release. Teacher overview displays eligible/waiting counts and setup readiness, with an owner-scoped Open Cycle 2 action; programme list/content views expose the same action. Release stores actor/time, tolerates repeats and never rewrites student results. Strict new programmes cannot publish/release with missing or unmapped work. Existing completed work remains reviewable.
- Standalone video activities support HTTPS links and a required Finish video lesson acknowledgement. It is explicit acknowledgement, not automatic watch-time/attention evidence. Completion validation occurs after the locked activity refresh; duplicate completions stay safe.
- Staged signup: teacher invitation + email → six-digit email code → private full name/LRN/password and optional mobile/grade/section → atomic code allocation/enrolment. Codes expire after ten minutes, have five-attempt limits, and share a sixty-second mailbox cooldown across initial sends/restarts/resends, plus mailbox/IP request limits. Session binding and one-use challenges prevent foreign sessions/replays. Invitation/code allocation is consumed only at final creation.
- Student names/LRNs are in an encrypted, hidden private identity payload; a keyed unique lookup protects duplicate LRN claims. Routine name/display stays the student code. Research/learning/teacher-progress views and exports remain coded. Email verification does not authenticate an LRN or school record. LRN accepts twelve digits, consistent with [DepEd Order 22, s.2012](https://www.deped.gov.ph/2012/03/20/do-22-s-2012-adoption-of-the-unique-learner-reference-number/); no DepEd registry connection exists.
- Existing legacy accounts retain credentials/history/access while an owner-controlled account-completion path is available. Newly teacher-created coded accounts must complete private details/email verification before learning; new public accounts finish verification before creation. Server middleware covers every learning/reading/document/save/submit route.
- Email/code sign-in, show/hide passwords, reset recovery, verified email-change confirmation, current-password checks, session lists/revocation and session-hash invalidation are implemented. Sensitive verification/password/LRN fields are excluded from flashed validation input. Student codes cannot be changed after assignment.
- Owner-only private profile settings (private photo also feeds the signed-in teacher’s native dashboard avatar), teacher display name/school/designation, private JPEG/PNG/WebP avatars with GD cropping/re-encoding, optional phone, read-only masked LRN and immutable code. Avatar filenames are random and original metadata is removed. Minimal account-security events record actor/action/time without private field values.
- Light/dark/system themes, standard/larger text, reduced motion and default cycle preferences persist. Shared head initialization covers public/login/workspace and Filament; native Filament theme initialization is synchronized. Saved reduced motion also stops landing JS choreography. Settings links are in teacher navigation/user menu and shared workspace/profile navigation.
- Teacher optional authenticator MFA uses installed Filament components and encrypted secrets/recovery codes. Regular ChemTrack sign-in delegates MFA-enabled teachers to the native panel challenge before authentication. Actual enabling/disabling/recovery checks used disposable synthetic accounts only.

## Rulings and history protection

Programme-wide release follows existing ownership boundaries. Module material remains configurable by activity/cycle; no invented same-versus-different module decision or automatic duplication. New programme creation uses strict stage sequencing. Old programmes keep their original within-cycle ordering and nullable mapping, while new cycle summaries expose missing setup and Cycle 2 dual gates still apply. Existing completion records remain reviewable. Frozen material is not silently remapped; create a revised programme when needed.

The old demo lacks some of the newly required four stages, so a lower cycle percentage/unavailable stage is expected. Original completion/attempt data was not erased or copied between cycles.

Three additive migrations add cycle workflow/release metadata, verification/private-identity fields and challenges, and preferences/avatar/pending-email/MFA/audit metadata. A private development dump and baseline aggregate counts were saved before migration. Baseline user/programme/activity/attempt/completion counts were checked unchanged afterwards. No account reset, real student import or submitted answer/result update occurred.

## Verification

- Root full Laravel suite: **93 tests / 640 assertions passed**, with GD enabled. Existing changed-spec tests were updated from combined totals to per-cycle/teacher-release expectations; no failing security behavior was relaxed.
- Frontend: **30 checks passed**, including actual installed Filament theme initializer interaction, corrupt/blocked storage fallback, saved reduced motion, landing interactions and reader/navigation recovery behavior. These are non-rendering tests.
- Pint passed; final Vite build passed. Approximate bundled output: app CSS91.3KB, teacher CSS717.4KB (74.9KB gzip), app JS20.9KB; PDF reader remains lazily imported. No new package dependency.
- Real local HTTP checks: teacher login/overview/programme list/roster/account-security/settings/reports, student code login/separate cycle rings/settings, signup and forgot-password entry pages. All succeeded; tests did not create actual student accounts or alter learning history/credentials.
- Independent task reviews found and fixed: unmapped strict activities, stale video acknowledgement, selected-cycle memory, absent CSV stage summaries, signup restart cooldown bypass, Filament theme-key mismatch and password-length mismatch. Final integrated reviewer independently passed **51 tests / 386 assertions**, with no remaining material finding in the checked scope.
- PHP GD was enabled from the existing XAMPP extension in the user's ChemTrack-specific PHP configuration, with the previous configuration backed up; global XAMPP PHP settings were not replaced.

## Current limits and next setup

Browser use reports disabled in Work settings. No new rendered screenshots, visual/mobile/responsive, full accessibility or actual-device checks are claimed. Earlier screenshots cannot establish this build's appearance.

Actual mail transport is currently `log`, not inbox delivery. Verification/recovery/email-change flows were tested with captured/fake notifications; configure and test a real sender before students register. Do not share passwords/provider secrets in chat or context files. See [mail setup](mail-and-account-readiness.md).

Original Google Forms response/result verification remains unconnected, and no real Forms/module/video materials were supplied. No Form-opening event marks completion. Teacher must supply/configure those sources and approved study notices; real roster matching/participation/retention arrangements remain institutional inputs.

The local site runs on loopback port8002 with the existing accounts. Database/site must stay running; no remote tunnel or production host was started. Production TLS, backups/restore, classroom load and live study validation remain outstanding.
