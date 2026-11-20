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
