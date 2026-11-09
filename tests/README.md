# Tests

Feature tests are in `tests/Feature/` and use an isolated in-memory SQLite database. Run `./scripts/dev.ps1 test`. Coverage includes role/ownership isolation, eligibility, scoring, saved answers, duplicate submissions, frozen content, teacher management and stale-activity regressions. The inherited trivial framework unit smoke test is also included in the suite count.

Run `./scripts/dev.ps1 integration` for the two-connection MariaDB content-freeze regression in `tests/Integration/freeze-current-read.php`. It requires the local development database and cleans only its own synthetic fixtures. Browser smoke scripts/screenshots remain ignored in `.local/` because they use generated local sign-in details. See `docs/development/first-build-evidence.md` for actual results and limits.
