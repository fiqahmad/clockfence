# ClockFence

**Employee QR attendance with geofence, for Laravel.**

Staff clock in and out with their own phone, with no app to install. The phone's location is checked against the staff member's own Site first. Only a pass issues a single-use token, and only then does the camera open to scan the static QR poster at the entrance. The security lives in that order of steps, not in the QR code.

> **Status: early development.** The foundation is in place (admin panel, roles, installer). The clock flow itself is being built in milestone M1. See the [milestones](https://github.com/fiqahmad/clockfence/milestones) for progress.

## Features (v1.0)

- **Clock in and out by phone**: a PWA in the browser, under 10 seconds per Clock.
- **Geofence first, QR second**: a photo of the poster is useless away from the Site; tokens last 90 seconds and work once.
- **Clear failure messages**: every refusal is named and tells the person what to do now. Distance and coordinates are never shown.
- **Sites**: many workplaces, each with its own fence, start time, working days and timezone.
- **Register**: one record per staff member per day: present, late (with minutes), absent, on leave, holiday.
- **Manual overrides**: by an Admin or Manager, always with a reason, never on their own record.
- **Reports**: daily board, monthly summary, CSV and XLSX export for payroll.
- **Privacy by design**: location is read once per Clock, never tracked in the background; coordinates are audit-only and pruned after 90 days.
- **English and Bahasa Melayu.**

**Not included, on purpose:** payroll, leave management, biometrics, native apps, background tracking, Google Maps, multi-tenancy.

## Requirements

| Need     | Version                                                                                 |
| -------- | --------------------------------------------------------------------------------------- |
| PHP      | 8.3 or newer, with `bcmath`, `gd`, `intl`, and `pdo_mysql`, `pdo_pgsql` or `pdo_sqlite` |
| Composer | 2.x                                                                                     |
| Node.js  | 20 or newer (to build the front end)                                                    |
| Database | MySQL 8, PostgreSQL 14+, or SQLite for a quick try                                      |
| HTTPS    | Required in production: phones only allow GPS and camera on HTTPS                       |

## Quick start

### Try it with demo data (SQLite)

```bash
git clone https://github.com/fiqahmad/clockfence.git
cd clockfence
composer setup
php artisan db:seed
php artisan serve
```

`composer setup` installs dependencies, creates `.env` (SQLite by default), migrates and builds the front end. The seeder prints the demo logins (one Admin, one Manager, three Staff members). Open `http://localhost:8000/admin`.

### Self-host as a normal Laravel app

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env && php artisan key:generate   # then set APP_URL and DB_* in .env
php artisan migrate --force
php artisan clockfence:install
php artisan optimize
```

Then run a queue worker (`php artisan queue:work --tries=3`) and the scheduler cron (`* * * * * cd /path/to/clockfence && php artisan schedule:run >> /dev/null 2>&1`).

A Docker setup is coming ([#6](https://github.com/fiqahmad/clockfence/issues/6)).

### Check the install

```bash
php artisan test --group=smoke
```

## Configuration

| `.env` key                  | Default | What it does                                                          |
| --------------------------- | ------- | --------------------------------------------------------------------- |
| `APP_URL`                   | none    | Must be the public HTTPS address in production                        |
| `CLOCKFENCE_TOKEN_TTL`      | `90`    | Seconds a Clock token stays valid                                     |
| `CLOCKFENCE_MAX_ACCURACY`   | `100`   | GPS fixes worse than this many metres are refused                     |
| `CLOCKFENCE_DEFAULT_RADIUS` | `120`   | Default geofence radius in metres for new Sites (allowed: 50 to 1000) |
| `CLOCKFENCE_CLOCK_OUT_GAP`  | `5`     | Minutes after a Clock-in before a Clock-out is accepted               |
| `CLOCKFENCE_RETENTION_DAYS` | `90`    | Days before location audit data is pruned                             |
| `CLOCKFENCE_DEVICE_BINDING` | `false` | Require a passkey-bound device (planned for M3)                       |

Keep `APP_TIMEZONE=UTC`; each Site sets its own timezone.

## Limitations

- **No overnight shifts in v1.** A day is judged in the Site's timezone, so a shift from 22:00 to 06:00 is recorded as two days. An Admin fixes it with a Manual override.
- **A browser cannot detect a mocked GPS location.** The geofence is a strong nudge, not proof.
- **One organisation per install.**

## Documentation

- [`GLOSSARY.md`](GLOSSARY.md): the domain language (Site, Clock, Geo-check, Attendance, ...)
- [`docs/adr/`](docs/adr): why key decisions were made
- [`CONTRIBUTING.md`](CONTRIBUTING.md): set up, test and submit a change
- [`SECURITY.md`](SECURITY.md): report a vulnerability or a spoofing bypass

## How this was built

Fiq wrote the plan, the domain model, the glossary and the decision records, and implemented the code with Claude Code guided by [`AGENTS.md`](AGENTS.md) and [`CLAUDE.md`](CLAUDE.md). The ADRs explain each decision so it can be discussed without any tool open.

## Licence

[MIT](LICENSE)
