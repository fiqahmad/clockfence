# Inertia/Vue app, vanilla JS clock page, Filament admin

The repo starts from the Laravel Inertia + Vue starter kit, which serves auth and the staff-facing pages. Two parts deliberately sit outside it. The clock page is a standalone Blade view with `public/js/clock.js`, because it has to load fast on low-end phones and the security-critical order of steps (Geo-check, Clock token, Poster scan) should be readable in one file without a SPA build. The admin panel at `/admin` is Filament, because it is far faster to build and familiar to Laravel contributors. The cost is three UI approaches in one app.

## Considered Options

- **Everything in Inertia/Vue**: one stack, but the admin CRUD, tables and exports would have to be hand-built.
- **Livewire starter kit + Filament**: one PHP-driven stack, but it would mean throwing away the existing Inertia/Vue skeleton.

## Later: QR decoding on iOS

Safari on iOS has no `BarcodeDetector`, so the clock page needs a JS QR decoder there. `jsqr` is bundled by Vite as a separate entry (`resources/js/qr-fallback.ts`) and loaded by `clock.js` only when `BarcodeDetector` is missing. `clock.js` itself stays free of dependencies, and Android phones never download the decoder.
