# Contributing to ClockFence

Thanks for helping. Bug reports, fixes, translations and docs are all welcome.

## Before you start

- Read [`GLOSSARY.md`](GLOSSARY.md) and use its terms (Staff member, Site, Clock, Geo-check, Attendance) in code, issues and PRs.
- Read the "Rules that never bend" in [`AGENTS.md`](AGENTS.md). A PR that loosens one of them will not be merged without an ADR in [`docs/adr/`](docs/adr).
- For anything bigger than a small fix, open or comment on an issue first so we agree on the approach.
- Found a way to clock in without being at the Site? That is a security issue: follow [`SECURITY.md`](SECURITY.md), do not open a public issue.

## Set up

Fork the repo, clone your fork, then:

```bash
composer setup        # install, .env, key, migrate (SQLite), build front end
php artisan db:seed   # demo logins
composer run dev      # app, queue, logs and Vite together
```

Laravel Sail works too: `./vendor/bin/sail up -d`, then prefix commands with `./vendor/bin/sail`.

## Make a change

1. Branch from `main`: `feat/<issue>-short-name`, `fix/...`, `docs/...`.
2. Write or update the test first and watch it fail. Feature tests for endpoints, unit tests for `Geofence` and `SiteTime`.
3. Keep domain logic in `app/Clock` and `app/Register`; keep controllers thin.
4. Put every user-facing string in `lang/en` and `lang/ms`.
5. Run the same checks as CI:

    ```bash
    composer ci:check     # formatting, lint, Larastan, Pint and Pest
    composer lint         # fix PHP formatting
    npm run check:fix     # fix front-end formatting
    ```

## Commit and open a PR

- Use [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `docs:`, `test:`, `ci:`, `chore:`.
- One concern per PR. Link the issue (`Closes #12`).
- Describe what changed and how you tested it. CI must pass before review.
- Add a line under `Unreleased` in [`CHANGELOG.md`](CHANGELOG.md) for anything a user would notice.

## Add a translation

1. Copy `lang/en` to `lang/<locale>`.
2. Translate every key; keep placeholders like `:name` unchanged.
3. Add the locale to the locale test and open a PR using the translation issue template.

## Using a coding agent

The repo ships [`AGENTS.md`](AGENTS.md) (all agents) and [`CLAUDE.md`](CLAUDE.md) (Claude Code). Agent-written PRs are welcome and held to the same standard: tests, passing CI, and a human who has read the diff.

## Code of conduct

This project follows the [Code of Conduct](CODE_OF_CONDUCT.md).
