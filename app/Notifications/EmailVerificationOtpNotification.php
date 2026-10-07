<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = [
            'title' => 'Vérifiez votre adresse e-mail',
            'greeting' => 'Bonjour '.$notifiable->name.',',
            'intro' => 'Saisissez ce code sur HerveShop pour confirmer votre adresse e-mail. Il est valable pendant 10 minutes.',
            'details' => ['Code de vérification' => $this->code],
            'actionUrl' => null,
            'actionText' => null,
            'closing' => 'Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail.',
        ];

        return (new MailMessage)
            ->subject('Votre code de vérification HerveShop')
            ->view('emails.notification', $data)
            ->text('emails.notification-text', $data);
    }
}
