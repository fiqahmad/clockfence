# Changelog

All notable changes to ClockFence are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- Laravel 13 app with Inertia and Vue, and a Filament admin panel at `/admin` for Admins and Managers.
- Roles (Admin, Manager, Staff) and a demo seeder.
- `php artisan clockfence:install` to create the first Admin.
- `config/clockfence.php` with the `CLOCKFENCE_*` settings.
- Smoke test group: `php artisan test --group=smoke`.
- CI on PHP 8.3 and 8.4 against SQLite, MySQL and PostgreSQL.
- Sites with a geofence, start time, timezone and Working days, managed by Admins and Archived instead of deleted.
- Staff members with a staff number, home Site, role and language, invited by email to set their own password, and deactivated instead of deleted.
- Geofence distance, Site day and lateness rules.
- The 16 named Clock failures with English and Bahasa Melayu messages.
- The Clock flow: a Geo-check issues a 90-second single-use Clock token, and scanning the Site Poster spends it and records a Clock-in or Clock-out with Late minutes.
- A printable A4 Poster per Site, in English and Bahasa Melayu.
