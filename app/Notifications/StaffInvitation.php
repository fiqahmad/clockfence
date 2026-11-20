<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Invites a Staff member to set a password and start clocking.
 */
class StaffInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public const VALID_FOR_DAYS = 7;

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $acceptUrl = URL::temporarySignedRoute(
            'invitations.show',
            now()->addDays(self::VALID_FOR_DAYS),
            ['user' => $notifiable],
        );

        return (new MailMessage)
            ->subject(__('invitations.mail.subject', ['app' => config('app.name')], $notifiable->locale))
            ->greeting(__('invitations.mail.greeting', ['name' => $notifiable->name], $notifiable->locale))
            ->line(__('invitations.mail.intro', ['app' => config('app.name')], $notifiable->locale))
            ->action(__('invitations.mail.action', [], $notifiable->locale), $acceptUrl)
            ->line(__('invitations.mail.expiry', ['days' => self::VALID_FOR_DAYS], $notifiable->locale));
    }
}
