---
name: clock-flow-change
description: Guard rails for any change in app/Clock, app/Register or public/js/clock.js. Use before editing the Geo-check, Clock token, Poster scan or AttendanceRegister.
---

# Change the Clock flow

The Clock flow is the security of ClockFence. Plan first, then change.

## 1. Name the rules you touch

List which of the 10 "Rules that never bend" in `AGENTS.md` the change affects, and say why each still holds. The ones that matter most here:

- Geo-check before token, token before camera, scan spends token. Clock-out uses the same steps.
- The Site comes from the Staff member's record, never from the request.
- The token is spent with one conditional UPDATE (`PhoneClock::spend`) before any other check.
- Only the SHA-256 hash of a token is stored.
- No distance, coordinates or tokens in any view, response or export (only the raw token in the Geo-check response, once).
- Location is read once per Clock (`getCurrentPosition`, never `watchPosition`); a failed Geo-check keeps no position.
- `AttendanceRegister` is the only writer of `attendances`; days and lateness go through `SiteTime`.

If the change needs to loosen a rule, stop and ask the maintainer; it needs an ADR in `docs/adr/`.

## 2. Test first

Add or change a test in `tests/Feature/Clock/ClockFlowTest.php` (or `tests/Unit/Clock/` for `Geofence` and `SiteTime`) and watch it fail.

## 3. Run the guard tests

```bash
php artisan test --compact --filter=Clock
```

These must stay green, unchanged:

- `spends a token only once, so a replay is refused`
- `lets only one of two requests with the same token win`
- `refuses an expired token and still spends it`
- `refuses another site poster and spends the token`
- `resolves the site from the staff member, never the request`
- `never sends distance or coordinates to the phone`
- `writes no attendance for any refused clock`

Then the full check: `composer ci:check`.

## 4. Browser changes

For `public/js/clock.js`: keep it dependency-free vanilla JS, keep the camera closed until a token arrives, and stop the camera on every exit path. Test on a real phone over HTTPS (for example with `php artisan serve` behind a tunnel), since GPS and camera do not work on plain HTTP.
