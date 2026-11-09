# ChemTrack workspace instructions

## Read before work

Read `README.md`, `docs/context/current.md`, and the relevant project documents. Preserve confirmed decisions and distinguish planned work from implemented behavior. The source discussion is the chat titled “ChemTrack Recommendation”; its retrieved content has been summarized locally. Do not assume access to missing earlier messages.

## After every user prompt

Before the final response:
1. Refresh `README.md` with the current status, date (Asia/Manila), meaningful changes, and next step. For a question with no project change, keep its summary concise and do not invent progress.
2. Synchronize `docs/context/current.md` with decisions, blockers, and immediate next actions.
3. Append a short dated entry to `docs/context/session-log.md` recording the request, work or answer, checks, and unresolved items. Summarize; do not copy full conversations.
4. Record lasting architecture/scope changes in `docs/decisions/README.md`.

Never write credentials, connection strings with secrets, identifiable student records, or private research responses into context files. If the user prohibits file changes in a later request, honor that restriction and mention that context was not updated.

## Scope and organization

- Keep Laravel at the project root once scaffolded; retain its standard directory conventions.
- Keep documentation in `docs/`, source assets in `assets/`, and superseded material in `archive/`. Use descriptive lowercase hyphenated document names. Add new navigation links to the appropriate index.
- Use synthetic data in examples and tests. Keep identifiable research data outside this repository.
- A local development application now exists. See `docs/development/README.md` and the build evidence. Do not claim a feature, deployment, or test passed without evidence.
- Preserve existing user work and customized skills. Do not publish, purchase hosting, push, or deploy unless requested.

## Product and engineering

- Planned stack: Laravel monolith, MySQL, Blade/Tailwind with selective Livewire for students, Filament for teachers. Verify compatibility before pinning versions.
- Enforce access and cycle/assessment eligibility on the server; hiding UI controls is insufficient.
- Preserve attempt history, timestamps, and submitted answers. Score on the server and protect against duplicate submissions.
- Design for mobile data: explicit saving feedback, recoverable requests, confirmed saved answers, and resumable attempts. Do not promise offline support.
- Prioritize behavior tests for role boundaries, scoring, eligibility, retakes, answer persistence, reconnection, and duplicate submission handling.

## Skills

Skills are installed globally in the user's Codex skills directory. See `docs/skills/README.md` and `docs/skills/sources.json`. Do not copy global skill payloads into the project or modify upstream instructions for project preferences.

Use Universal Web Dev to route substantial web work. Hallmark provides an optional visual direction when relevant; Diagram Design is for standalone visual explanations. Choose one coherent approved design direction; do not combine multiple competing design manuals. Installing a skill does not require running it on unrelated tasks.

Use Universal Web Dev's `--sync` mode for updates, preserving modified/unmanaged skills. Never use its overwrite override without explicit user intent. Read the installed skill's current instructions when actually applying it. Optional PostgreSQL guidance does not change this project's MySQL decision.
