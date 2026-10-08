<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Confirmez votre adresse e-mail - HerveShop')
                ->view('emails.notification', [
                    'title' => 'Confirmez votre adresse e-mail',
                    'greeting' => 'Bonjour '.$notifiable->name.',',
                    'intro' => 'Pour terminer la création de votre compte HerveShop, confirmez votre adresse e-mail en cliquant sur le bouton ci-dessous.',
                    'details' => [],
                    'actionUrl' => $url,
                    'actionText' => 'Confirmer mon e-mail',
                    'closing' => 'Ce lien est valable pendant 60 minutes. Si vous n’êtes pas à l’origine de cette inscription, ignorez simplement ce message.',
                ]);
        });

    }
}
