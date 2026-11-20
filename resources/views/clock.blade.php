<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#18181b">
    <title>{{ __('clock.page.title') }} · {{ config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/clock.css">
</head>
<body>
    <header class="top">
        <div>
            <p class="greeting">{{ __('clock.page.greeting', ['name' => $staffMember->name]) }}</p>
            @if ($site)
                <p class="site">{{ __('clock.page.site', ['site' => $site->name]) }}</p>
            @endif
        </div>
        <nav>
            @if ($canOpenAdmin)
                <a href="/admin">{{ __('clock.page.admin_link') }}</a>
            @endif
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link">{{ __('clock.page.logout') }}</button>
            </form>
        </nav>
    </header>

    <main id="clock" class="card" aria-live="polite">
        <p id="clock-status" class="status"></p>
        <div id="clock-camera" class="camera" hidden>
            <video id="clock-video" playsinline muted></video>
            <p id="clock-countdown" class="countdown"></p>
        </div>
        <button id="clock-action" type="button" class="primary" hidden></button>
        <button id="clock-cancel" type="button" class="secondary" hidden>{{ __('clock.page.cancel') }}</button>
    </main>

    <p class="hint">{{ __('clock.page.install_hint') }}</p>

    <script type="application/json" id="clock-config">@json($clockConfig)</script>
    <script src="/js/clock.js" defer></script>
</body>
</html>
