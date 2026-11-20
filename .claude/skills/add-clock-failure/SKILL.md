---
name: add-clock-failure
description: Add a new named check-in failure (a new way a Clock can be refused). Use whenever a change adds a reason to reject a Clock.
---

# Add a Clock failure

Every refusal is a `ClockFailure` case. Server and browser read the same catalogue, so a failure added in one place only is a bug.

## Steps

1. Add the case to `app/Enums/ClockFailure.php`: TitleCase name, snake_case value (e.g. `case DeviceNotBound = 'device_not_bound';`). Decide `retryable()`: true only if the Staff member can succeed by trying again without an Admin or Manager.
2. Add the message to `lang/en/clock.php` and `lang/ms/clock.php` under `failures`. Say what happened and the fix the person can do now. Never mention distance, coordinates, metres or tokens.
3. Throw `new ClockRefused(ClockFailure::YourCase)` from the place in `app/Clock/` or `app/Register/` where the rule lives. Keep the order: Geo-check, token, scan (see `/clock-flow-change`).
4. If the browser detects it (permissions, device features), raise it in `public/js/clock.js` with `showFailure('your_case')`. The message comes from the catalogue, so no copy goes in JS.
5. Update the expected count in `tests/Feature/Clock/ClockFailureTest.php`.
6. Add a feature test in `tests/Feature/Clock/` that triggers the failure and asserts the `data.failure` value and that no Attendance was written.
7. Run `php artisan test --compact --filter=Clock`, `vendor/bin/pint --dirty`, and `vendor/bin/phpstan analyse`.

## Done when

- The case is in both lang files and in `ClockFailure::catalogue()`.
- The new test fails without the change and passes with it.
- `CHANGELOG.md` has a line under Unreleased.
