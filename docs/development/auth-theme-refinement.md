# Account entry and theme refinement

4 November 2026, Asia/Manila. Implemented locally at the user's request.

## Behavior

Landing, sign-in, signup, verification and recovery use the original light teal/off-white theme. A device dark-mode preference or another account's browser cache no longer changes public pages. Unconfigured accounts also start light. After login, explicit account appearance preferences still apply; Settings → Appearance saves light/dark/system, text and motion preferences. Existing saved preferences were not reset. Native teacher login stays light until authentication. Corrected missing dark surfaces/text and hover states in the workspace/result/teacher UI.

Shared account entry layout places sign-in on the right and signup/verification/details on the left on wide screens. Smaller screens use one form column. One shared logo and a top-left, destination-labeled Back button replace scattered return text. Account access links remain ordinary server navigation. Supporting browsers can animate the panel position using cross-document View Transitions; other browsers retain the working layout with a short form entrance. Reduced motion disables these effects.

Real labels float to the input border on focus, entered values or autofill; focus borders animate without hiding the label. Each password has its own in-field eye control, accessible action/pressed state and input association. Passwords are never rendered back from submitted values. Without JavaScript the reveal button stays hidden and the password input remains usable. The shared controls also cover account-settings passwords. Signup includes Email → Verify → Details step context and a factual invitation/teacher-account explanation.

## Signup explanation

This is invitation-based enrolment, not a manual approval queue. The existing teacher opens **Students → Create student invitation**, chooses an owned programme, then privately shares the generated link. The single-use invitation expires after seven days. The student verifies their email first, supplies private name/LRN/category/password, then receives a permanent code and enrolment after successful final validation. No new approval gate was added or removed.

Public signup creates students only. Teachers currently use accounts provisioned by the project maintainer; there is no teacher self-registration route. This update explains the existing restriction and does not create another teacher or expose a public teacher-role selector. Existing teacher/student credentials and learning records remain unchanged.

The local mail transport remains `log`. It captures verification/recovery messages locally rather than delivering to an inbox. Real signup still requires [sender configuration](mail-and-account-readiness.md).

## Evidence and limits

- Full application suite: **96 tests / 697 assertions passed**.
- Frontend behavior: **33 checks passed**, including light defaults, account theme isolation, live system changes, native teacher theme initialization and independent password reveal controls.
- Pint and Vite build passed; no dependencies or migrations added.
- Actual HTTP checks passed for teacher/student login, workspaces, separate-cycle summaries, settings, reports, MFA page, signup and recovery entry pages. No learning records or credentials changed.
- Eight source palette foreground/background pairs measured above 4.5:1 (auth helper text 6.06:1; primary action 7.58:1). This does not establish computed/rendered contrast everywhere.
- Browser inventory explicitly reported local viewing disabled in Work settings. Fresh screenshots, transition playback, autofill appearance, responsive layout, keyboard traversal and real-phone checks remain pending. No visual-completion claim is made.

Next: review `/login` and `/register` in the local browser, including light/dark account settings, narrow widths and reduced motion. Configure/test real mail delivery before inviting actual students. Google Forms/materials remain pending separately.
