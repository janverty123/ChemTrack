# Teacher workspace update

30 October 2026 (Asia/Manila). User requested that the teacher interface match the student style and make programmes and individual students easier to access.

Keep Filament's existing content/account management and server-side ownership policies. Apply the accepted teal/off-white palette, Manrope/Inter local fonts, shared logo, rounded controls and native fully collapsible navigation through a compiled Filament theme. Keep the light learning-workspace appearance consistent across teacher pages.

Replace the generic welcome dashboard with an overview of the teacher's own programme/student counts, programme links (content and progress), recent student-code links, and direct creation/invitation actions. Do not add invented study metrics or imply the Google Forms connection is ready.

Add a read-only individual student overview, accessible from both dashboard and roster rows: only teacher-owned programmes, confirmed completion, per-activity state, saved reading estimates, native scores and timestamps. Reuse the existing report calculations through one service so the individual view and programme matrix agree. Never display student names/email in the overview or expose their form data in its Livewire state. Keep account management/reset as a separate existing action.

Keep existing enrolment, content-freeze, invitation, native assessment and export behavior. Add useful programme-page links to progress/enrolment without bypassing permissions or revising frozen content. No schema changes, material imports, user resets or production deployment.

Validation: ownership/privacy and profile/report behavior tests, existing teacher/account/upload/report regressions, asset build and formatter, live teacher dashboard/roster/profile/programme checks at wide/narrow sizes. Preserve the user's open create-programme form while testing in a separate tab. Use synthetic fixtures; no real student data in docs.
