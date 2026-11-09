# ChemTrack

ChemTrack is a chemistry remediation and progress-tracking web application for students learning at home at their own pace, with a teacher dashboard for content, assessments, progress, and research results.

**Status:** student and teacher workspaces implemented locally with fictional demonstration content. Not deployed or validated for a live study. Last context update: **9 November 2026 (Asia/Manila)**.

**Latest verification (4 November):** the auth/theme refinement passed **96 application tests / 697 assertions**, **33 frontend checks**, Pint and the Vite build. Actual HTTP teacher/student sign-in/workspace/settings/report/signup/recovery checks passed. Browser viewing is disabled in Work settings, so this update has no fresh visual/mobile validation. See [auth/theme evidence](docs/development/auth-theme-refinement.md); earlier [cycle/account evidence](docs/development/cycle-accounts-settings-evidence.md) remains historical.

**Latest availability:** local database/site restarted on 4 November at **http://127.0.0.1:8002/**. Existing demo accounts remain valid. Three additive migrations were applied after a private database backup; original user/programme/activity/attempt/completion counts are unchanged. Keep both processes/laptop running. Remote tunnel remains stopped.

## Work from your phone

Open the [GitHub web editor](https://github.dev/janverty123/ChemTrack), sign in, and use **Source Control → Commit & Push** to save changes to GitHub. The editor handles source changes. Running ChemTrack requires a PHP development environment and a database. See the [development setup](docs/development/README.md).

## Start here

**Latest work:** public/account entry pages now default to the original light theme; customization applies after sign-in. Shared sign-in/right and signup/left layouts include top-left Back controls, floating labels, individual in-field password toggles and progressive panel transitions. Account theme preferences, cycle gates, private identities and existing histories/credentials remain preserved.

**Latest request:** prepare the complete site source for the GitHub repository so it can be edited from a phone. The source snapshot includes the Laravel app, frontend assets, migrations, dependency lockfiles, tests and setup documentation. Local environment files, credentials, databases and uploads are excluded. Signup uses a teacher invitation to choose the programme, then email verification and private details; there is no manual approval queue. Teacher self-registration is not implemented; teacher accounts are maintainer-provisioned. Next: review the local forms visually. Real inbox verification still needs a mail sender (`log` currently), and real Google Forms verification/materials are not connected. See [mail and account setup](docs/development/mail-and-account-readiness.md).

| Document | Purpose |
| --- | --- |
| [Auth/theme refinement](docs/development/auth-theme-refinement.md) | Light public defaults, account form interactions, signup explanation and verification limits |
| [Cycle/account/settings evidence](docs/development/cycle-accounts-settings-evidence.md) | Implemented behavior, migration/history safeguards, checks and limits |
| [Mail and account readiness](docs/development/mail-and-account-readiness.md) | Real sender setup, verified signup, legacy rollout and MFA access |
| [Cycle progress, accounts and settings plan](docs/project/cycle-progress-accounts-settings-plan.md) | Approved plan executed locally; remaining external inputs and validation are recorded in evidence |
| [Presentation review](docs/development/presentation-polish.md) | Circular teacher progress, site polish, checks and presentation limits |
| [Current context](docs/context/current.md) | Decisions, open questions, and next steps |
| [Session log](docs/context/session-log.md) | Concise record of requests and outcomes |
| [Product scope](docs/project/product.md) | Users, learning flow, and acceptance criteria |
| [Teacher requirements and feature plan](docs/project/teacher-requirements-feature-plan.md) | Screenshot findings, proposed features, scroll tracking, Forms choices and build order |
| [Student codes and Form verification](docs/project/student-codes-and-form-verification.md) | Selected native Forms approach, code-only student accounts and separate G/B numbering |
| [Custom tests → Google Forms history](docs/project/google-forms-custom-interface.md) | Earlier feasibility investigation; custom write bridge superseded by native Forms |
| [How to open the site](docs/development/open-local-site.md) | XAMPP/launcher steps, local sign-in details and troubleshooting |
| [Remote phone preview](docs/development/remote-preview.md) | Temporary protected access preparation, current readiness and pending inputs |
| [Architecture](docs/project/architecture.md) | Stack, data design, connectivity, and hosting |
| [System planning figures](docs/diagrams/system-planning-overview.md) | Discussion draft: architecture, learning sequence, save/resume flow |
| [Landing-page reference](docs/design/pantas-landing-reference.md) | Pantas motion analysis, header/menu requirements and asset plan |
| [Landing refinement](docs/design/landing-refinement.md) | User-requested softer shapes, contained hero, app motion and oval menu |
| [Typography and theme](docs/design/typography-and-theme.md) | Font hierarchy, colour tokens and original visual preview |
| [Dashboard and reader progress](docs/design/dashboard-and-reader-progress.md) | Overall/task circles, cycle switch, PDF viewing and oval stage tracker |
| [Workspace navigation](docs/design/workspace-navigation.md) | Retractable sidebar, narrow drawer, styled Sign out and verification |
| [Teacher workspace evidence](docs/development/teacher-workspace-evidence.md) | Matching teacher theme, programme/student access, privacy and checks |
| [Teacher workspace plan](docs/development/teacher-workspace-plan.md) | Scope and implementation contracts for the teacher update |
| [Roadmap](docs/project/roadmap.md) | Implementation stages and their status |
| [Exploratory pricing](docs/project/pricing-notes.md) | Possible research-use packages and estimate assumptions |
| [Decision log](docs/decisions/README.md) | Reasons behind important choices |
| [Development guide](docs/development/README.md) | Setup status, conventions, and checks |
| [First-build plan](docs/project/first-build-plan.md) | Implementation scope and demonstration assumptions |
| [Build evidence](docs/development/first-build-evidence.md) | Actual tests, browser checks and remaining limits |
| [Site-update evidence](docs/development/site-update-evidence.md) | Current implementation, regression checks and pending integrations |
| [Site-update plan](docs/development/planned-site-update.md) | Scoped implementation contracts and completion status |
| [Skills guide](docs/skills/README.md) | Installed skills, routing, and safe updates |
| [Installation report](docs/skills/installation-2026-10-28.md) | What was installed and verified |
| [Agent instructions](AGENTS.md) | Rules for future work and context updates |

## Current application

- **Students:** invited staged email verification precedes private name/LRN/password signup and automatic G/B code assignment. Each cycle has its own four-stage total and activity list; module assessments, reading coverage, completion and scores remain distinct. Existing legacy/demo access is preserved during the controlled account-completion rollout.
- **Teachers:** own programme/student management, setup/eligible/waiting counts and explicit Cycle 2 release. Students still need completed Cycle 1. New programmes require complete cycle setup before publication; frozen content/history remains protected. Reports/CSV show separate cycle totals and stage status, identified by code.
- **Accounts/settings:** owner-only private profile/avatar, verified email changes, password recovery/change, sessions, light/dark/system, larger text/reduced motion and default cycle. Optional teacher MFA uses installed Filament authenticator/recovery components.
- **Resilience/integrity:** server permissions and stage eligibility, confirmed saving/retry/resume, immutable submitted native answers/scores and duplicate-safe finalization remain. Internet is required.

One module/two cycles remain; its per-cycle material can be configured without auto-copying content or completion. Video completion currently uses explicit acknowledgement, not watch-time proof. Real Form reads/materials and study policies are pending. The mailer is currently `log`; real students cannot complete inbox verification until sender delivery is configured/tested. Existing coded demo accounts remain usable locally.

## Planned stack

Laravel 13.39.0 monolith with Blade and Tailwind 4.3.3 student screens, Filament 5.11.0 / Livewire 4.4.8 for teachers, and Vite 8.3.5. Uses the MySQL database driver with local MariaDB 10.11.16; SQLite isolates automated feature tests. Composer and pnpm lockfiles pin the resolved packages. Student answer saving uses small standard browser requests.

Start with Laravel-compatible PHP shared hosting. Cloudflare may be added as a DNS/security/CDN layer; a Workers/D1 rewrite, Redis, WebSockets, long-running queue workers, a separate React app, and native mobile applications are outside Version 1.

## Folder map

```text
ChemTrack/
  AGENTS.md                 future assistant instructions
  README.md                 project entry point and latest status
  docs/
    context/                current context and session history
    project/                scope, architecture, roadmap
    decisions/              durable decisions and rationale
    development/            setup and verification instructions
    skills/                 inventory, installation report, source pins
    design/                 approved visual direction and mockups
    diagrams/               architecture and learning-flow diagrams
    research/               blank instruments and anonymized examples
  assets/                   project source assets; not runtime public files
  tests/                    behavior and integration checks
  archive/                  superseded documents retained for reference
```

The Laravel application lives at the repository root, using standard `app/`, `routes/`, `resources/`, `database/`, `public/`, and supporting framework directories. `scripts/dev.ps1` provides local commands. `.local/` holds ignored local credentials, logs and screenshots.

## Running the project

**Open locally now:** [ChemTrack on this laptop](http://127.0.0.1:8002/), or [sign in](http://127.0.0.1:8002/login). This loopback server uses a separate local session cookie. The remote preview is stopped. It must be restarted after a reboot; the ordinary launcher below still uses port 8000.

For ordinary laptop-only startup, open [the local preview](http://127.0.0.1:8000/). Original fictional-account details remain in ignored `.local/demo-accounts.json`; the new coded sample account is in `.local/site-update/updated-preview.json`. No passwords belong in documentation. Original learning history is unchanged by this update.

The old phone preview is stopped. This laptop link requires only the ChemTrack account; no permanent `.env` change was made.

To reopen later, start MySQL in XAMPP and double-click `Start-ChemTrack.cmd`; keep its window open. It serves only this computer on port 8000. Do not start a second server if the preview is already running. Follow the [step-by-step opening guide](docs/development/open-local-site.md), or see [development setup](docs/development/README.md) for other commands. Never copy `.env` or `.local/` into a public site or repository.

## Hosting context

The “ChemTrack Recommendation” chat preferred Z.com Starter and discussed Z.com Single and ZOOM.ph SWIFT as alternatives. Its price estimates are historical planning inputs, not a current quote or a purchased service. Recheck price, billing term, domain cost, PHP support, SSH/Composer access, document-root control, extensions, backups, resource limits, and cron support before choosing a host. Avoid storing large videos directly on a small shared-hosting account.

## Keeping context current

After each prompt, update this README's status/date/next step as needed, synchronize [current context](docs/context/current.md), and append a concise outcome to [the session log](docs/context/session-log.md). Record durable changes in the decision log. Keep sensitive information and student records out of these files. This is a working convention enforced through `AGENTS.md`, not a background automation.

**Next step:** review locally via [Teacher](http://127.0.0.1:8002/teacher) and [Settings](http://127.0.0.1:8002/settings). Configure/test an approved mail sender before real signups, supply the approved materials and Forms connection, then complete visual/phone/accessibility/load/restore checks. No hosting or deployment was performed.
