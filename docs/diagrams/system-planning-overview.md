# System planning figures

Status: discussion draft, updated 29 October 2026 (Asia/Manila). Teacher screenshots now confirm two cycles; the precise instrument arrangement remains open. A first local application implements the shared architecture and a configurable demonstration sequence. The new cycle/Form/scroll features below are planned, not implemented. See the [feature proposal](../project/teacher-requirements-feature-plan.md) and [existing build evidence](../development/first-build-evidence.md).

## Figure 1 — Application architecture

```mermaid
flowchart TD
    Student[Student on phone or computer] --> StudentUI[Student interface: Blade / Tailwind / selective Livewire]
    Teacher[Teacher] --> TeacherUI[Teacher dashboard: Filament]
    StudentUI --> Access[Laravel authentication and permissions]
    TeacherUI --> Access
    subgraph App[One Laravel application]
        Access --> Learning[Remediation content and eligibility]
        Access --> Assessment[Assessment attempts and scoring]
        Access --> Reporting[Progress and research reports]
    end
    Learning --> DB[(MySQL)]
    Assessment --> DB
    Reporting --> DB
```

Both interfaces use the same application and database. Server rules control access; students cannot bypass eligibility or view other students' records by changing URLs. Proposed service responsibilities do not imply separate servers or microservices.

## Figure 2 — Single-module assessment gate: placement/format pending

```mermaid
flowchart TD
    Login[Sign in] --> Assignment[Overall dashboard with Cycle 1 / Cycle 2 switch]
    Assignment --> Module[One module: cycle mapping pending]
    Module --> Part[Current unlocked part]
    Part --> Assessment[Within-module assessment: original direction, format pending]
    Assessment --> Submit[Submit required assessment]
    Submit --> Confirm{Server confirms submission?}
    Confirm -->|No| Retry[Keep next part locked; recover or retry]
    Retry --> Assessment
    Confirm -->|Yes| Remaining{More required module parts?}
    Remaining -->|Yes| Part
    Remaining -->|No| Finish[Module finish under agreed final rules]
```

One module and two cycles are confirmed; how the single module is divided or used across the cycles remains open. This diagram shows the original within-module assessment direction, with exact formats/counts/locations undecided. Submission-gated advancement is confirmed; a pass score or teacher-grading prerequisite is not. Pre-/post-test, video and survey placement are separate unresolved details; external Forms return-flow planning remains deferred. A draft save or scroll percentage cannot satisfy a submission gate.

## Figure 3 — Saved answers and connection recovery

```mermaid
sequenceDiagram
    participant S as Student browser
    participant L as Laravel
    participant D as MySQL
    S->>L: Save answer for owned active attempt
    L->>L: Check ownership, eligibility and attempt status
    L->>D: Persist validated answer
    D-->>L: Saved
    L-->>S: Confirm saved
    Note over S,L: If confirmation fails, show unconfirmed save and allow a safe retry
    S->>L: Reconnect and resume attempt
    L->>D: Read confirmed saved answers
    D-->>L: Persisted progress
    L-->>S: Resume with confirmed progress
```

The existing native demonstration implements confirmed saves, retries and final server scoring; no full offline mode is promised. Proposed external Forms submissions need their own verification contract. Timers, retakes, feedback release and local drafts remain design questions.

## Figure 4 — Separate reading, completion and performance

```mermaid
flowchart TD
    Scroll[Module reading and scrolling] --> Reading[Save last and furthest reading position]
    Reading --> Indicator[Bottom-centre reading indicator]
    Reading --> Observation[Estimated reading signal]
    Checkpoints[Required checkpoints and agreed finish rule] --> Completion[Server-confirmed module completion]
    Forms[Verified Form response or native submission] --> Test[Confirmed test completion and score if available]
    Completion --> Compliance[Required activities completed / assigned]
    Test --> Compliance
    Observation --> Tracker[Student and teacher progress views]
    Compliance --> Tracker
    Test --> Results[Assessment results: separate measure]
```

Reading signals remain separate from compliance and scores. Reaching the bottom cannot submit an assessment, invent an external result or independently unlock the next cycle. The exact module finish rule is a proposal awaiting confirmation.

## Planning agenda

| Topic | Baseline / discussion needed |
| --- | --- |
| Study and learning flow | Two cycles of chemistry remediation at home; confirm test placement, content counts and completion rules |
| Accounts and access | Student and teacher; confirm enrollment, class scope and administrator needs |
| Content | Modules, videos, short assessments; confirm formats and teacher publishing workflow |
| Assessment rules | Confirm item types, scoring, attempts, timers, feedback, eligibility and overrides |
| Student screens | Proposed activity dashboard, cycle view, module reader with bottom-centre scroll progress, video, test/Form return, survey and results/progress |
| Teacher screens | Proposed students/classes, content, assessments, monitoring, research reports |
| Data model | Preserve versioned assessments, attempts, answers, progress and timestamps |
| Research reports | Confirm which attempts count, cohort comparisons, missing-data handling and exports |
| Reliability | Confirmed saves, resume, safe retries and duplicate-submission prevention |
| Hosting and maintenance | Compatible PHP hosting; optional Cloudflare layer; current compatibility/cost checks later |
| Implementation | Agree on rules and design, then plan complete usable build stages with acceptance tests |

Current decisions: confirm instrument placement in the two cycles, external Form verification and module/video completion rules. Dashboard and visual design follow the feature-plan discussion.
