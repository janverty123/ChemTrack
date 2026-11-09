# Email and verified account readiness

4 November 2026 (Asia/Manila).

## Current state

The verified signup, recovery and email-change routes are implemented. The current local mailer is `log`: Laravel captures messages locally and does not deliver them to inboxes. A real student cannot complete inbox verification until a sender is configured. Do not present a log capture as delivered email.

## Connect the sender

Use an approved transactional mail service or school SMTP account. Put its settings only in the private environment file/server secret store. The configured Laravel mail transports support SMTP through `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`; choose TLS/scheme and port from the actual provider instructions. Do not commit provider credentials or copy them into project context. Restart/refresh configuration and the local server after changing these settings.

Verify sender/domain ownership with the selected provider, then test signup code delivery, resend delay, expiration, spam-folder handling, recovery links and email-change notifications with authorized test recipients. Use the exact URL/domain students will access so links and secure cookies match. Real inbox/provider deliverability was not tested in this build.

## Account behavior

There is **no manual teacher approval queue**. The invitation selects the teacher/programme before signup. The teacher uses Students → Create student invitation (`/teacher/student-invitations`), chooses an owned programme and privately shares the generated link. Each invitation is single-use and expires after seven days. A completed signup automatically consumes it and enrols the student. Teacher accounts themselves are currently provisioned by the project maintainer; public teacher self-registration is not implemented. Main sign-in accepts a provisioned teacher's email.

Public landing/auth screens and unconfigured accounts start light. Appearance customization belongs to the authenticated account's Settings → Appearance; saved choices are retained. See [account entry refinement](auth-theme-refinement.md).

New public students start with a valid teacher invitation and email. They verify the code before entering name/LRN/password; the account/code/enrolment is created only after final successful validation. Sharing or guessing an existing student code cannot claim the account. Email verification confirms mailbox control, not a school record.

Teacher-created coded accounts sign in with their existing assigned code/password, then complete email verification/private details before learning. Existing legacy/demo accounts preserve access during the controlled rollout and can use `/account/complete`; no fabricated name/LRN/email is assigned to them. Continue using synthetic demo accounts until real mail/study setup is approved.

Teachers can open Settings for private profile, appearance, email/password and sessions. Optional teacher authenticator setup/recovery is under `/teacher/account-security`. Existing credentials and the private Desktop demo-account note remain valid; no MFA was enabled on a real existing account during this build.

Research/learning views and CSVs identify students by permanent code; real details remain owner-only account information. Teacher administrative corrections/roster matching beyond existing ownership require an explicit process; there is no public LRN lookup or live DepEd connection.
