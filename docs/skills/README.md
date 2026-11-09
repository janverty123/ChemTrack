# Skills guide

## Installation and location

36 skills were installed globally in `~/.codex/skills/`: Universal Web Dev, its 33 pinned companion skills, Diagram Design, and Hallmark. The project keeps navigation and source records here; skill payloads remain outside this folder. Built-in and plugin skills were not changed.

See [the installation report](installation-2026-10-28.md) and [exact source revisions/inventory](sources.json).

## Choosing skills

| Work | Guidance |
| --- | --- |
| Web planning, implementation, review | Universal Web Dev routes to the relevant installed companions |
| A new visual direction | Hallmark when requested/relevant; retain an approved direction once selected |
| Standalone architecture or process diagram | Diagram Design |
| Small architecture diagram maintained in Git | Mermaid in Markdown |
| Backend work | API/security guidance, plus official Laravel documentation |

Examples: `use universal-web-dev to plan ChemTrack's next feature`, `use hallmark to propose a student dashboard direction`, `use diagram-design to explain the assessment flow`.

Do not load all skills for every task. Do not combine competing visual systems. PostgreSQL guidance is installed because all profiles were requested, but ChemTrack remains planned for MySQL.

## Safe updates

For Universal Web Dev, obtain a fresh checkout, read `INSTALL.md`, inspect its installer and lockfile, preview `--profile all --sync`, apply that same plan, and run doctor. Sync preserves modified/unmanaged skills and updates only unchanged installer-managed modules to the release's reviewed pins. Do not use `--update` to overwrite local edits without explicit authorization.

Diagram Design and Hallmark were installed with Codex's skill-installer helper at recorded commits. Future updates must compare the current tree with the recorded per-file hashes in `sources.json`, preserve local changes, and stage/review any replacements. Their standalone copies are not registered with marketplace or `npx skills` auto-updaters.

Keep ChemTrack preferences in the root `AGENTS.md` and project documents so upstream skill files can remain unchanged.

## Runtime limits

- Skill instructions are installed; full execution/integration is untested.
- Impeccable is guidance-only under Universal Web Dev's installer. Its separate optional binary/launcher was not installed.
- Hallmark browser checks require Chrome and `puppeteer-core`; availability has not been verified or newly installed.
- Diagram Design export/render workflows may require additional dependencies; these were not installed.
- Native Diagram Design plugin commands were not installed; its standalone skill and support files were installed.
- Skills should be available on the next turn. If the current chat still shows a cached catalog, start a new chat or refresh Codex.
