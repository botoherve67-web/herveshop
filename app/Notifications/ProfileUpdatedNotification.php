<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileUpdatedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Profil HerveShop mis à jour')
            ->view('emails.notification', [
                'title' => 'Votre profil a été mis à jour',
                'greeting' => 'Bonjour '.$notifiable->name.',',
                'intro' => 'Les informations de votre profil HerveShop ont été mises à jour avec succès.',
                'details' => [],
                'actionUrl' => route('account.dashboard'),
                'actionText' => 'Vérifier mon profil',
                'closing' => 'Si vous n’êtes pas à l’origine de cette modification, contactez rapidement notre équipe.',
            ]);
    }
}