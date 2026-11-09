# Product scope

## Latest implemented scope — 4 November

User approved and executed the [cycle/account/settings plan](cycle-progress-accounts-settings-plan.md). Independent cycle totals and pre-test/module/video/post-test, teacher release in addition to completed Cycle 1, staged email verification/private identity, settings/themes/recovery and optional teacher MFA are implemented locally. See [evidence](../development/cycle-accounts-settings-evidence.md) for migration compatibility, tests and limits. Mail delivery/real Forms/materials remain unconnected; historical paragraphs below retain earlier planning context and do not override the new cycle/account contracts.

## Purpose and users

Help students complete chemistry remediation at home at their own pace and help teachers review learning progress and research outcomes.

| User | Planned access |
| --- | --- |
| Student | Assigned content, eligible assessments, own attempts/scores/progress |
| Teacher | Assigned students, content and assessment management, progress and reporting |

Whether a separate administrator role is needed remains open.

The teacher workspace now matches the student's palette, fonts and logo and provides direct programme/progress/student access. Individual student overviews keep reading estimates, completion and scores separate. Started programmes can be viewed read-only and enrolled without bypassing the existing edit freeze. See [teacher implementation evidence](../development/teacher-workspace-evidence.md); this is local functionality, not live-study approval.

User confirmed code-only student identities with automatic registration assignment and separate sequences for girls (`ST1-G`, `ST2-G`, …) and boys (`ST1-B`, `ST2-B`, …). Student invitation registration, code/password login, separate installation-wide counters and teacher-assisted reset are implemented. Existing fictional accounts keep their credentials/history and use neutral references until coded. Account and pending Forms contracts are in [student codes and Form verification](student-codes-and-form-verification.md).

## Proposed learning flow

The six teacher-conversation screenshots supplied on 29 October confirm **two cycles**, self-paced at-home remediation, locked prerequisites, modules with short assessments, video lessons and a completion tracker. They also identify Google Forms for pre-/post-tests and an experience survey. The user requests a bottom-centre module scroll indicator connected to progress tracking; visual/dashboard design comes later.

The user has now specified the dashboard: one overall circle spanning both cycles, a Cycle 1/2 task-list switch and a smaller circle per task. The reader uses a bottom-centre oval progress outline with stage text inside. A custom scrollable private PDF reader with saved distinct-page viewing estimates is implemented locally; OCR-to-native formatting is possible but requires reviewed conversion. See [dashboard/reader design](../design/dashboard-and-reader-progress.md). External Form return-flow planning is deliberately deferred for a later discussion.

The user now confirms **one module**. Its mapping to Cycle 1/2 is not yet specified; do not create one module per cycle or assume it is repeated. The original direction places assessments within that module. Assessment types, counts and locations are undecided, but students must submit the required assessment before moving to the next gated part. Confirmed submission is distinct from saving a draft; no pass mark or teacher-grading wait has been specified.

Working module flow: open an eligible section → read/work → complete its assessment in the module interface when configured → submit → server confirms submission → next required part unlocks. Pre-/post-tests remain the selected native Google Forms/summative-test direction; their exact placement across cycles and transition flow are not settled in this clarification. Video/survey placement also remains open.

See the [teacher requirements and feature plan](teacher-requirements-feature-plan.md) for source distinctions, the full feature inventory, reading/compliance/score definitions, implementation stages and unresolved choices. That document preserves the proposal history; [current evidence](../development/site-update-evidence.md) distinguishes implemented accounts, rings, reader and reports from pending study/provider configuration. Its native pre-/post-test scoring does not constitute a Google Forms connection.

## Version 1 acceptance targets

- Students can access only their own results and assigned material; teachers see only authorized classes/students.
- The server enforces prerequisites, retake rules, and assessment availability.
- Answers have visible saving/saved/error feedback; students can resume confirmed saved progress after reconnecting.
- Submissions and retries cannot create duplicate final attempts or corrupt scores.
- Scores are computed on the server against a defined assessment version. Submitted attempt history remains auditable.
- Teacher reports can pair verified pre-/post-test outcomes without losing original attempts or treating missing external responses as zero.
- Reading position, completed requirements and assessment scores remain separate measures. Scroll position is an estimated reading signal, not proof of comprehension or an assessment submission.
- Required Google Forms steps unlock successors only under an agreed verification contract; a link click alone cannot confirm completion.
- Research pre-/post-tests use the original Google Form screen, with actual response/result verification in ChemTrack. The earlier custom Form-submission bridge is superseded. Use a reliable participant/test reference, not student real names, to match responses.
- Core flows work on phones and with a keyboard, including slow/error states.

These remain live-study acceptance targets. Local automated evidence covers a subset; it does not establish provider integration, device accessibility or research readiness. Exact research instruments, content, privacy/consent process, retention policy, and reporting/export requirements must be supplied or confirmed.

## Deferred

Full offline assessments/synchronization, native apps, separate React frontend, real-time sockets, Redis, microservices, and features requiring persistent queue workers.
