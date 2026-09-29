<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue sur HerveShop')
            ->view('emails.notification', [
                'title' => 'Bienvenue sur HerveShop',
                'greeting' => 'Bonjour '.$notifiable->name.',',
                'intro' => 'Votre compte a été créé avec succès. Vous pouvez dès maintenant découvrir nos produits et suivre vos commandes depuis votre espace personnel.',
                'details' => [],
                'actionUrl' => route('account.dashboard'),
                'actionText' => 'Accéder à mon compte',
                'closing' => 'Merci de votre confiance et bienvenue dans la communauté HerveShop.',
            ]);
    }
}