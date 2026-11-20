<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $site->name }} · {{ config('app.name') }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #111; background: #f4f4f5; }
        .sheet { width: 180mm; min-height: 267mm; margin: 10mm auto; padding: 12mm; background: #fff; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .brand { font-size: 14pt; letter-spacing: .08em; text-transform: uppercase; color: #52525b; }
        h1 { font-size: 30pt; margin: 6mm 0 2mm; }
        .site { font-size: 20pt; font-weight: 600; margin-bottom: 8mm; }
        .qr { width: 120mm; height: 120mm; }
        .qr svg { width: 100%; height: 100%; }
        .languages { display: grid; grid-template-columns: 1fr 1fr; gap: 8mm; margin-top: 10mm; text-align: left; width: 100%; }
        .languages h2 { font-size: 13pt; margin: 0 0 2mm; }
        .languages ol { margin: 0; padding-left: 5mm; font-size: 12pt; line-height: 1.5; }
        .actions { text-align: center; margin: 6mm; }
        .actions button { font: inherit; padding: 3mm 6mm; border-radius: 2mm; border: 0; background: #111; color: #fff; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; width: auto; min-height: auto; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">{{ __('posters.print') }}</button>
    </div>
    <main class="sheet">
        <div class="brand">{{ config('app.name') }}</div>
        <h1>{{ __('posters.title', locale: 'en') }}<br>{{ __('posters.title', locale: 'ms') }}</h1>
        <div class="site">{{ $site->name }}</div>
        <div class="qr">{!! $qrSvg !!}</div>
        <div class="languages">
            @foreach (['en' => 'English', 'ms' => 'Bahasa Melayu'] as $locale => $languageName)
                <section lang="{{ $locale }}">
                    <h2>{{ $languageName }}</h2>
                    <ol>
                        @foreach (__('posters.steps', locale: $locale) as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        </div>
    </main>
</body>
</html>
