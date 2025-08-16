<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail //implements ShouldQueue
{
    // use Queueable;

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('E-Mail-Adresse bestätigen')
            ->greeting('Grüäzi und Hallo auf lose-your-weight!')
            ->line('Bitte klicke auf den Button unten, um deine E-Mail-Adresse zu bestätigen.')
            ->action('E-Mail-Adresse bestätigen', $verificationUrl)
            ->line('Falls du kein Konto erstellt hast, musst du nichts weiter tun.')
            ->salutation('Viele Grüsse');
    }
}
