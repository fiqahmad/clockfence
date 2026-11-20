---
name: install-clockfence
description: Install and configure ClockFence on this machine (demo, Docker or production). Use when someone asks to install, set up or run ClockFence for the first time.
---

# Install ClockFence

## 1. Ask one question

Which mode?

- **demo**: SQLite and seeded demo logins, for trying it locally.
- **docker**: `docker compose` with MySQL, a queue worker and the scheduler.
- **production**: an existing server with PHP, a database and HTTPS.

For docker and production, also ask for `APP_URL` and the database password, or ask the user to put them in `.env` themselves.

## 2. Check the machine

Run and report:

- `php -v` (need 8.3+) and `php -m` (need `bcmath`, `gd`, `intl`, and `pdo_sqlite`, `pdo_mysql` or `pdo_pgsql`)
- `composer -V` and `node -v` (need 20+)
- docker mode only: `docker compose version` (PHP and Node are then not needed on the host)

If something is missing, say what and how to install it on this OS, then stop. Do not install system packages without asking.

## 3. Install

If `.env` exists, leave it alone and only add missing `CLOCKFENCE_*` keys from `.env.example`. Never print its secrets.

- **demo**: `composer setup`, then `php artisan db:seed`.
- **docker**:
    1. If there is no `.env`: `cp .env.example .env`, then set `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false` and `DB_PASSWORD`.
    2. `docker compose build`
    3. If `APP_KEY` is empty: `docker compose run --rm --no-deps -T -e AUTORUN_ENABLED=false app php artisan key:generate --show` and write the last line into `APP_KEY`.
    4. `docker compose up -d --wait`
    5. `docker compose exec app php artisan clockfence:install`
- **production**:
    1. `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`
    2. If there is no `.env`: `cp .env.example .env` and `php artisan key:generate`; set `APP_URL` and `DB_*`.
    3. `php artisan migrate --force`, `php artisan clockfence:install`, `php artisan optimize`

## 4. Verify

- **demo**: `php artisan test --group=smoke` must pass.
- **docker**: `curl -fsS http://localhost:${APP_PORT:-8000}/up` succeeds and `docker compose ps` shows every service healthy.
- **production**: `php artisan about` shows the production environment and the right database; `APP_URL` starts with `https://` (GPS and camera need it); `curl -fsS $APP_URL/up` succeeds. The smoke tests need dev dependencies, so do not run them here.

## 5. Report

Tell the user the URL (`/admin` for the admin panel), the admin login (demo: the accounts the seeder printed) and the "First run" steps in the README. For production, remind them to set up the queue worker and the scheduler cron, and show both lines:

```bash
php artisan queue:work --tries=3
* * * * * cd /path/to/clockfence && php artisan schedule:run >> /dev/null 2>&1
```

## Never

- Print or commit secrets from `.env`.
- Run `migrate:fresh`, `db:wipe` or drop tables on a database that has data.
- Edit app code to get past an install error. Report the error instead.
