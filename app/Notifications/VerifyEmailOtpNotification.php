<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtpNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $otp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Code de verification HerveShop')
            ->view('emails.notification', $this->data($notifiable))
            ->text('emails.notification-text', $this->data($notifiable));
    }

    private function data(object $notifiable): array
    {
        return [
                'title' => 'Code de verification',
                'greeting' => 'Bonjour '.$notifiable->name.',',
                'intro' => 'Utilisez ce code pour confirmer votre adresse e-mail et terminer votre inscription.',
                'details' => [
                    'Code OTP' => $this->otp,
                    'Validite' => '10 minutes',
                ],
                'actionUrl' => null,
                'actionText' => null,
                'closing' => 'Si vous n avez pas cree ce compte, ignorez ce message.',
            ];
    }
}
