<?php

use App\Enums\ClockFailure;
use Illuminate\Support\Facades\App;

it('has exactly the 16 agreed failures', function () {
    expect(ClockFailure::cases())->toHaveCount(16);
});

it('has a message for every failure in every locale', function (string $locale) {
    App::setLocale($locale);

    foreach (ClockFailure::cases() as $failure) {
        expect(trans()->hasForLocale($failure->messageKey(), $locale))
            ->toBeTrue("Missing {$locale} message for {$failure->value}");
    }
})->with(['en', 'ms']);

it('never mentions distance, coordinates or tokens', function (string $locale) {
    App::setLocale($locale);

    foreach (ClockFailure::cases() as $failure) {
        expect(mb_strtolower($failure->message()))
            ->not->toMatch('/distance|jarak|coordinat|koordinat|latitud|longitud|token|\d+\s?(m|metre|meter)\b/');
    }
})->with(['en', 'ms']);

it('sends the whole catalogue to the page as JSON in the current locale', function () {
    App::setLocale('ms');

    $catalogue = json_decode(ClockFailure::toJson(), true, flags: JSON_THROW_ON_ERROR);

    expect(array_keys($catalogue))->toBe(array_map(fn (ClockFailure $failure) => $failure->value, ClockFailure::cases()))
        ->and($catalogue['outside_fence']['message'])->toBe(__('clock.failures.outside_fence', locale: 'ms'))
        ->and($catalogue['outside_fence']['retryable'])->toBeTrue()
        ->and($catalogue['account_inactive']['retryable'])->toBeFalse();
});
