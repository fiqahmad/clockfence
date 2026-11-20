<?php

namespace App\Enums;

/**
 * Every named reason a Clock can be refused. Server and browser read the same
 * catalogue, so a failure added in one place only is a bug.
 */
enum ClockFailure: string
{
    case InsecureContext = 'insecure_context';
    case LocationDenied = 'location_denied';
    case LocationUnavailable = 'location_unavailable';
    case LocationTimeout = 'location_timeout';
    case WeakFix = 'weak_fix';
    case OutsideFence = 'outside_fence';
    case NoHomeSite = 'no_home_site';
    case AccountInactive = 'account_inactive';
    case TooManyAttempts = 'too_many_attempts';
    case CameraDenied = 'camera_denied';
    case PosterUnreadable = 'poster_unreadable';
    case WrongSite = 'wrong_site';
    case TokenExpired = 'token_expired';
    case TokenInvalid = 'token_invalid';
    case AlreadyClockedOut = 'already_clocked_out';
    case ClockOutTooSoon = 'clock_out_too_soon';

    public function messageKey(): string
    {
        return 'clock.failures.'.$this->value;
    }

    /**
     * The message shown to the Staff member: what happened and the fix they can do now.
     */
    public function message(): string
    {
        return __($this->messageKey());
    }

    /**
     * Whether the Staff member can succeed by trying again themselves, without an Admin or Manager.
     */
    public function retryable(): bool
    {
        return match ($this) {
            self::InsecureContext,
            self::NoHomeSite,
            self::AccountInactive,
            self::AlreadyClockedOut => false,
            default => true,
        };
    }

    /**
     * Every failure with its message in the current locale, keyed by case value.
     *
     * @return array<string, array{message: string, retryable: bool}>
     */
    public static function catalogue(): array
    {
        $catalogue = [];

        foreach (self::cases() as $failure) {
            $catalogue[$failure->value] = [
                'message' => $failure->message(),
                'retryable' => $failure->retryable(),
            ];
        }

        return $catalogue;
    }

    /**
     * The catalogue as JSON, for the clock page.
     */
    public static function toJson(): string
    {
        return json_encode(self::catalogue(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
