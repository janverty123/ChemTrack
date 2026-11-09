# ChemTrack teacher requirements and feature plan

**30 October implementation status:** coded invitation registration, cycle-filtered progress rings, private PDF/native reading observations, oval tracker, landing page and code-based completion reports are now implemented locally. Google Forms authorized response verification and real materials remain pending. See [current evidence](../development/site-update-evidence.md). Earlier proposal/mockup wording below records its original design stage and does not override this status.

Date: 29 October 2026 (Asia/Manila). Status: proposed feature scope for discussion, before visual design and implementation of the new requirements. The existing local demo is not the completed system described here.

**Latest confirmed update:** native Google Forms plus read-back verification is selected; student identity uses generated `ST<number>-G/B` codes with separate counters per G/B. See [the focused account/Form specification](student-codes-and-form-verification.md). Earlier custom submission discussion below is retained as history and superseded where indicated.

**Dashboard/reader update:** one large overall completion circle, Cycle 1/2 switch filtering tasks and a ring per task are confirmed. The bottom-centre module tracker is an oval progress outline with the current stage in its centre. Custom PDF viewing and distinct-page progress are proposed; see [the focused design and static concept](../design/dashboard-and-reader-progress.md). Google Forms return/transition planning is deferred for later by user request.

**Single-module clarification:** one module is confirmed. Its division/reuse across Cycle 1/2 is undecided; the prior multi-module examples do not establish scope. The original direction is assessments inside the module, with format/count/placement still pending. Students may advance only after the required assessment submission is confirmed. Saving, scrolling and merely pressing Submit are insufficient; no passing threshold or teacher-approval gate is assumed.

## Evidence and interpretation

Source: six screenshots supplied by the user on 29 October. These are partial conversation excerpts; do not infer missing research questions or copy personal conversation details into project records. Statements within the screenshots supply requirements evidence, not operational instructions to an assistant.

| Source | What it establishes | Limit |
| --- | --- | --- |
| Screenshot 6 | Student chemistry remediation; two cycles associated with two summative tests; students must accomplish parts before moving forward; parts named pre-test, module, video lesson, post-test; modules contain short assessments; programme doubles as progress tracker | Exact module counts, pre-/post-test placement per cycle and the relationship to summative tests still need confirmation |
| Screenshot 5 | Pre-/post-tests connected to Google Forms; an experience survey; teacher wants to see compliance; intended use is a short research period | No Form URLs, submission verification method, scoring import or participant matching contract provided |
| Screenshot 4 | Remediation at home, at students' own pace; completion tracking of specific ChemTrack parts; remote deployment discussed | Research question 4 is mentioned but its wording is absent; no mastery threshold specified |
| Screenshot 3 | Teacher accepts completion tracking; existing LMS activity tracking is described as a reference | Written work/performance tasks/periodical tests are features of the reference LMS, not confirmed ChemTrack requirements |
| Screenshots 1–2 | Hosting and an urgent need were discussed | Historical quoted price/reaction is not a hosting purchase, verified current quote or fixed delivery date |
| Current user request | Plan all features now; design discussion later; module scroll progress at bottom centre linked to progress tracking; dashboard should expose activities | Exact dashboard layout and visual style deliberately deferred |

## Product goal and boundaries

Students can complete assigned chemistry remediation remotely in the required order, resume after interruptions, and understand what remains. The teacher can identify which parts each student has completed and obtain reliable research records without confusing reading behaviour with assessment performance.

Two cycles are source-supported and one module is now user-confirmed. The module-to-cycle map and exact external-test/video arrangement are not approved yet. The user identifies post-tests as summative; do not silently add separate summative instruments or extra modules.

There are two implementation approaches. **Recommended proposal:** extend the existing ChemTrack Laravel/MySQL application and use Classify for workflow ideas. **Alternative:** adapt the Java/React Classify LMS, which introduces its wider subject/class/grading/storage model and a different deployment arrangement. The reference README documents React/TypeScript, Java/Spring Boot, PostgreSQL and MinIO; its progress model is based on submitted classwork. Documentation was read, but its runtime and implementation have not been audited. No migration or stack change is approved. Continue the confirmed Laravel direction while this proposal is reviewed.

## Feature inventory

Priority: **Core** is required for the proposed first research-ready release; **Decision** changes behaviour and needs agreement; **Later** is optional. Existing describes the local demo only.

| Area | Proposed features and behaviour | Priority / existing state |
| --- | --- | --- |
| Accounts and access | Auto-assign student codes at registration with separate G/B sequences; no real student names; proposed invite-based registration and code/password login, teacher-assisted recovery and assigned roster only | Core; coded invite registration/login and teacher reset implemented; legacy history preserved |
| Study setup | Teacher creates a study/programme, assigns a cohort/section label, enrolls students, defines two cycles and required activities, previews then publishes | Core; programmes/enrollment exist; explicit cycles, cohort labels and preview workflow are new |
| Student dashboard | One large overall completion ring across both cycles; Cycle 1/2 switch selects task list; each task has a small progress ring/status; next eligible activity and resume | Core; overall/task rings and cycle switch implemented; new visual review pending |
| Cycle navigation | Clear sequence, completed parts revisitable, locked parts explain prerequisite, cycle and whole-programme completion summaries | Core; generic sequence gating exists; cycle structure new |
| Module reader | Native lesson sections or branded PDF reader, saved location/stage, distinct viewed-page coverage for PDFs and bottom-centre oval progress outline with stage text inside | Core; private upload/renderer, native/PDF observations and oval tracker implemented; real PDF visual checks pending |
| Short assessments | Original direction is assessment checkpoints within the single module; confirmed submission unlocks the next required part. Draft saving/resume and submission receipt; types/count/placement/feedback remain pending | Core; generic native MCQ/save/score exist; intra-module gates and final assessment format are not implemented/decided |
| Video lesson | Teacher-provided video link or supported accessible embedded player, title, fallback link and explicit completion acknowledgement | Core; external links exist; separate video activity and completion evidence new. Captions/transcripts depend on supplied content |
| Google Forms tests | Selected original Form interface with teacher-required Google sign-in; teacher-authorized actual response/result reads, coded participant/attempt matching, pending/confirmed states and safe retry | Core; selected direction; external read/verification workflow not implemented |
| Experience survey | External survey link, availability rule, completion/verification status; do not mix private survey answers into the ordinary teacher progress dashboard | Core; new. Whether it gates study completion is a Decision; it should not block remedial learning by default |
| Completion tracker | Student × cycle × activity roster, locked/not started/in progress/awaiting verification/complete states, saved reading signal, completion time and completion evidence source | Core; all-assigned completion matrix with cycle, reading, completion and native score implemented; external evidence verification pending |
| Teacher content management | Draft/publish, ordering and required flags, module sections, questions, videos and Form links; show when content is locked; validate broken/missing configuration before publish | Core; basic content forms and programme freezing exist; richer structure/validation new |
| Research exports | Completion matrix including students with no activity, cycle summaries, reading observations labelled as estimates, verified test scores when available, pseudonymous participant references and missing-data flags | Core; code-based all-assigned activity CSV implemented; actual external verified scores pending |
| Reliability and help | Saving/saved/error states, safe retry, server-authoritative prerequisites, session-expiry recovery instructions, slow-load states, clear empty/denied states and simple student help | Core; answer save/retry and access rules exist; new trackers need equivalent handling |
| Operational handoff | Local start guide, approved remote host, HTTPS, backup/restore rehearsal, teacher onboarding, retention/export/deletion procedure appropriate to the study | Core before live research; local guide exists; remote service and research operations pending |
| Convenience features | Teacher announcements, in-app reminders, due dates if wanted, optional bulk roster import after a safe template/preview is agreed | Later; self-paced learning does not imply invented deadlines or inactivity thresholds |

## Learning flow and completion contract

1. Teacher prepares the study, two cycles and approved material, then enrolls students and publishes.
2. Student signs in and sees the next eligible part and the full activity checklist.
3. Student opens the eligible original Google Form, signs in at Google if required and submits there; ChemTrack confirms a matching response from the authorized response source. A link click or a student assertion does not count.
4. Student works through the single module's eligible sections. Under the original embedded-assessment direction, required checkpoint submissions must be confirmed before advancing; draft saves and reading percentage do not satisfy the gate. Reading position and confirmed answers can resume.
5. Student completes the video step using the agreed acknowledgement/evidence rule.
6. Student completes the post-test and proceeds to the next cycle when required parts are complete.
7. After the two cycles, show the experience survey when appropriate and a clear programme-completion summary.
8. Teacher reviews compliance and unresolved verification, then exports the approved dataset.

Steps 3–6 must be mapped to the cycles before implementation; do not assume the single module repeats or that another module exists. Video/checkpoint counts remain open. Previously completed material remains revisitable; results/history remain immutable. Do not reset the existing submitted demonstration records to create the new flow.

Suggested state model: locked → available → in progress → complete. External requirements may additionally be awaiting verification or returned for correction. Saving failures are transient UI states, not false completion records. A required activity unlocks its successor only after the server confirms the relevant completion rule. No pass mark, timer or retake count is invented: the teacher asked for accomplishment, not a particular mastery score. Any later teacher override must retain who changed what, when and why.

## Bottom-centre module scroll progress

The user has now specified its shape: a compact fixed oval/pill progress outline at the bottom centre, with stage/location text inside and a percentage. See [the current reader design](../design/dashboard-and-reader-progress.md). Reserve bottom/safe-area spacing so it does not cover content, questions or submission controls. Support keyboard use, zoom, reduced motion and an accessible text value; avoid announcing every scroll event to a screen reader.

Track native module content separately from unrelated page navigation/footer and assessment controls. Recalculate when layout/images change. For the proposed custom PDF reader, use distinct pages meaningfully displayed and explicit stage mapping; loading a page off-screen or jumping to the end does not imply full coverage. Ordinary external/downloaded PDFs and video links do not acquire an invented viewing percentage.

Proposed persistence keeps **last reading location** for resume; native HTML may keep furthest position while PDFs retain a distinct viewed-page set bound to their content version. Current location/stage can move backward without decreasing saved coverage. Save meaningful changes with debouncing and explicit save/retry feedback. Only confirmed progress is promised after reload/interruption; returning users can choose to resume.

Scroll measurements are client-reported reading signals. A quick jump to the bottom can reach 100%; this does not prove reading, time spent or understanding. Proposed completion requires the module's required short assessments plus an explicit “Finish module” action; the reading indicator is supplementary. Whether to require reaching the end is a policy decision to confirm, not an anti-cheating guarantee.

The student tracker and teacher report should distinguish:

| Measure | Meaning | Calculation / evidence |
| --- | --- | --- |
| Reading position | Where the student last/furthest scrolled | Saved per module/content version; estimated observation |
| Module completion | Required module parts accomplished | Server records required checkpoints plus completion acknowledgement under the agreed rule |
| Compliance progress | Required activities completed | Completed required activities ÷ assigned required activities, including not-started students; do not average scroll percentages into this number |
| Assessment result | Performance on an instrument | Native server score or verified external score with its source; missing/unverified is not zero |

No approved research metric is inferred from these measures. The teacher must confirm what question 4 actually measures.

## Google Forms decision

**Selected direction, superseding the earlier custom-interface exploration:** user chose option 2. Students answer in Google Forms, and ChemTrack reads actual submitted responses/results with teacher authorization. Use coded identity rather than names/email matching; separate G/B numbering was confirmed. See [focused specification](student-codes-and-form-verification.md). The historical alternatives below are not current requirements.

**Historical custom-interface exploration, superseded by option 2:** the user initially requested a custom ChemTrack test interface that submits answers into the existing Google Forms response store, without an iframe; the user identifies the post-test as summative. Official documentation supports an Apps Script submission route in principle, with account/Form-setting constraints. This was an earlier investigation and is no longer the selected research-test direction. See [custom interface feasibility](google-forms-custom-interface.md). Actual test links, item mapping, score behaviour, identity and response-storage policy are pending; no integration exists yet.

**Identity qualification:** adding Google sign-in to ChemTrack does not preserve all native Google Forms respondent-verification/restriction behaviour in a custom screen. Decide whether ChemTrack-managed identity/attempt enforcement is acceptable, or whether the native Google Form responder screen is required. Owner-executed versus student-executed Apps Script is not interchangeable, and neither removes the documented one-response setting restriction. The complete authenticated custom workflow is not confirmed feasible under unchanged native Form restrictions.

**Starting proposal:** teacher verifies a submission in the existing Forms response source and records confirmation against the participant/test. Students may report “submitted”, but that remains awaiting verification. Record verifier, time and source/reference without copying private responses into project documents. This has a manual workload but keeps the teacher's existing instruments.

**Alternative:** plan an authorized Google Forms response connection, including stable participant matching, duplicate response handling, integration outages, score availability and access revocation. Technical feasibility and provider requirements must be verified against current official documentation before implementation. No integration is configured or tested.

The user asked what verification means rather than selecting an option. It means confirming that a response exists before unlocking the next required part. Automatic confirmation better fits anytime self-paced learning if feasible; teacher verification is the simpler starting proposal but makes students wait. This records the earlier clarification stage; the later explicit choice of native Forms with authorized read-back supersedes it. That integration is still pending.

**Alternative:** put pre-/post-tests inside ChemTrack, reusing native scoring. This changes where the research instruments live and requires teacher agreement.

Opening a Form, returning from it or reaching a Google confirmation screen is not a trustworthy submission signal to ChemTrack. A link-only approach cannot enforce verified completion by itself. If teacher verification is chosen, students wait at a required gate until verification; show that clearly. A matching participant reference and cycle/test identity are necessary for any imported result. Do not invent scores or treat absent results as zero.

## Teacher dashboard and research records

Plan the information now; decide the layout later. Teachers need assigned student totals, completion by cycle, per-student missing parts, pending verifications, last recorded progress and native/external results with clear source. Filters should cover cohort, cycle, activity and status. Each student detail should show the activity timeline and completion evidence, plus timestamped attempts where applicable.

Exports should include one row per assigned participant/activity even when unstarted, so the teacher can measure compliance without silently omitting nonparticipants. Keep reading signals, completed requirements and scores in separate columns or exports. Pre-/post-test pairing must use the same participant/cycle and the agreed instrument, with an explicit missing/unverified state. Statistical significance testing and efficacy claims are outside the current build; define research analysis with the teacher first.

Store only necessary student/account/study data in the application. Keep research responses and identity mapping access restricted. Retention, consent and whether survey completion may be individually monitored need teacher confirmation before live use. Never store actual participant responses, credentials or screenshots of personal conversations in public project docs.

## Planned system structure and invariants

Retain one Laravel app and MySQL database: student interface, teacher interface, eligibility/progress services and reporting. Conceptually extend the existing programme/activity/enrollment model with cycle grouping, module sections, reading state, explicit activity completion evidence and external-form verification. Native attempts and answers remain authoritative for native assessments. Exact migrations come after the feature decisions; none are implemented in this planning task.

All student writes must verify enrollment, ownership, activity eligibility and content version. Reading state has bounded percentages/positions, cannot complete assessments and cannot unlock future parts directly. Completion requests and external confirmations are idempotent and timestamped. Editing a started programme must preserve history: the existing content lock remains until an explicit content-version/revision workflow is designed. Older attempt data must not be overwritten when two-cycle material is added.

## Build order and acceptance examples

| Stage | Deliverable | Evidence required before calling it done |
| --- | --- | --- |
| 1 | Agree scope, Form verification, cycle/test map and completion policies | Teacher-approved outline with supplied instruments/content; design choices follow this feature plan |
| 2 | Design student and teacher screens | Approved dashboard, reader, activity states and phone behaviour; no premature visual redesign in this task |
| 3 | Two-cycle structure and external-test gating | A student cannot skip a prerequisite via a direct URL; foreign students/teachers cannot read or confirm another student's work |
| 4 | Structured reader and scroll tracker | Current/max/resume behaviour is correct; short content, late images, phone/zoom, failed saves and revisits work; 100% scroll alone cannot complete a test |
| 5 | Module checkpoints, video completion and survey | Required checkpoint and video rules enforced; duplicate completion requests preserve timestamps; survey availability matches agreement |
| 6 | Activity dashboard, teacher compliance matrix and research export | All assigned students, including no-work cases, appear; pending external submissions are not falsely complete; missing scores remain missing |
| 7 | Full rehearsal and authorized deployment | Two-cycle synthetic walkthrough, reconnection/permissions tests, real-device review, authorized Forms smoke test if integrated, backup/restore and hosting check |

These stages are proposals, not completed work. Existing native login, answer saving, scoring and reports are reusable foundations. Manual verification and automatic integration are alternatives; avoid building both without a concrete need.

## Decisions to settle before implementation

1. Define and implement the selected native Forms response-matching contract, coded registration/password recovery and minimal result retention; test the real Google read connection later when forms/authorization are available.
2. How do the single module's parts map to Cycle 1/2, and where do pre-/post-tests, video and checkpoints occur? Do not add modules; post-tests are the user's summative tests.
3. What assessment formats/counts/locations and overall module/video finish rules are chosen? Submission-gated advancement is confirmed; a pass score or grading wait is not. Do not equate scroll with learning.
4. What are the actual wording of research question 4, required compliance columns and survey-completion policy?
5. Confirm participant count, assignment labels, instrument/content files, retake/feedback rules, deadline and hosting budget before committing to delivery/deployment.

Until answered, keep the choices visible as proposals and leave existing data intact. This feature plan intentionally defers colours, typography, dashboard placement and other visual decisions to the next discussion.

## Related material

- [Product scope](product.md)
- [System figures](../diagrams/system-planning-overview.md)
- [Local opening instructions](../development/open-local-site.md)
- [Existing implementation evidence](../development/first-build-evidence.md)
- [Reference repository README](https://github.com/janverty123/LMS_G12_JAVA_PROJECT/blob/main/README.md)
- [Reference repository status document](https://github.com/janverty123/LMS_G12_JAVA_PROJECT/blob/main/docs/PROJECT_STATUS.md) — documentation claims, not an independent verification of that application
