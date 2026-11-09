# Planned site update: implementation and evidence

Updated 30 October 2026 (Asia/Manila). Implements the [site-update plan](planned-site-update.md), with synthetic content and the existing Laravel/MySQL architecture. This is a development preview, not approval for a live study.

## Implemented

- Original chemistry landing page with scroll reveals, a normal scrolling header and separate fixed top-right menu; self-hosted Manrope headings and Inter body text, reduced-motion rules and responsive styles.
- Invitation-based code/password registration without student name/email. Separate installation-wide G/B counters, one-use hashed seven-day invitations, transactional enrollment and teacher ownership checks. Existing legacy credentials/history remain valid; neutral references hide their names in learning/reporting screens until assigned a code.
- One overall completion ring across both cycles, Cycle 1/2 task-list selection and individual task rings. Eligibility is computed over the full sequence before filtering. Legacy activities remain explicitly unassigned.
- Native lesson and private PDF reader with a bottom-centre oval tracker, current stage/page, saved position and distinct-page/furthest-viewed estimates. Saves are bounded, versioned, recoverable and separate from completion. PDF.js loads only for PDFs. Content freezes once observations/attempts/completions begin.
- Teacher-scoped private PDF uploads and protected student delivery. File-path tampering is rejected during metadata/signing and persistence. Existing native assessment saving/final submission remains intact.
- Google Forms source/link configuration and setup-pending states. External tests cannot be submitted or completed through native endpoints. No live Google authorization, response receipt, matching or score sync exists yet.
- Teacher completion matrix/CSV includes every assigned learner, including no-work cases. Codes, cycle, reading estimate, completion and native score remain distinct; formula-like CSV values are escaped.

## Data preservation

Additive migrations were applied to the local development database. An ignored recovery SQL dump was created during preparation and verified before adding new sample content. Baseline original records (three users, one programme, five activities, four attempts and two completions) were preserved; original accounts and frozen activities were not rewritten.

A separate fictional coded account and four-part **one-module** programme illustrate two cycle groups without enrolling existing learners in it. The original sample also remains. Sample grouping and questions do not establish research policy. Credentials and recovery files stay under ignored `.local/`; this document contains no passwords or participant records.

## Checks

| Check | Evidence |
| --- | --- |
| Full Laravel suite | 30 October: **41 tests / 254 assertions passed** |
| Reader state/regressions | 30 October: **7 Node tests passed**, including backtracking before and during a pending save |
| Formatting | 30 October: Pint check passed |
| Production assets | 30 October: Vite build passed; local font assets and lazy PDF/worker bundles generated |
| Private upload regression | Covered in full suite: foreign-path metadata/save denied; persisted own file and actual new PDF upload accepted |
| Version/access/reading | Covered in full suite: server eligibility, monotonic coverage, stale-version rejection, private delivery and external-test bypass denial |
| MariaDB content freeze | 30 October: fresh two-connection current-read regression passed |
| MariaDB registration concurrency | 30 October: fresh real two-process distinct-invite/same-invite rehearsals passed using five disposable prefixed tables; original counts/counters/invitation usage unchanged |
| Protected HTTP flow | 30 October: local and public gate tests passed for denials, assets, actual login, repeated dashboard requests, landing navigation, logout and private paths |
| Public updated flow | 30 October: new HTTPS address passed protected landing/assets/fonts, coded login, both cycle views, unchanged zero overall completion, reader/oval, cross-cycle direct-access denial, logout and name-free registration |

The gate regression was updated for the new dashboard's “Overall completion” label and additionally requests the landing page. Its initial 30 October failure was an obsolete text assertion after the redesign, not evidence that the password gate lost authentication.

Independent task reviews approved coded accounts, the learning foundation, student UI and landing scope. Review findings about private upload paths and furthest native reading persistence were reproduced, fixed and rereviewed. The broader final integration reviewer reported no new material defect before a usage-limit interruption; that review did not produce a final approval artifact. The primary agent completed integration checks; do not describe the interrupted review as a finished independent approval.

## Verification limits and next work

Browser viewing is disabled in Work settings. No bypass was used. New-page visual appearance, actual PDF canvas/zoom behaviour, keyboard/screen-reader interaction and real-phone overlay/reconnection behaviour have **not** been visually verified. HTTP checks and pure state tests do not replace those checks. Earlier first-build browser evidence applies to the earlier screens only.

Real module materials, part-to-cycle mapping and assessment format/count/placement remain undecided. Google Forms authorized read-back/matching, OCR/import, teacher review of converted content, retakes and correction/version workflows remain pending. No pass-score or teacher-grading prerequisite was invented. A whole PDF is one eligible activity; this is not a contract for hiding later pages inside an otherwise delivered PDF.

The temporary password-protected preview is already authorized. Restarting it changes its address; `.local/phone-preview-url.txt` and the private handoff carry the active URL. Laptop/database/app/gate/tunnel must stay running. No paid hosting, production deployment, push or publication beyond that preview was performed.
