<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
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

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Reinitialiser votre mot de passe - HerveShop')
                ->view('emails.notification', [
                    'title' => 'Reinitialiser votre mot de passe',
                    'greeting' => 'Bonjour '.$notifiable->name.',',
                    'intro' => 'Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.',
                    'details' => [
                        'Validite' => '60 minutes',
                    ],
                    'actionUrl' => $url,
                    'actionText' => 'Modifier mon mot de passe',
                    'closing' => 'Si vous n avez pas demande cette action, ignorez ce message.',
                ])
                ->text('emails.notification-text', [
                    'title' => 'Reinitialiser votre mot de passe',
                    'greeting' => 'Bonjour '.$notifiable->name.',',
                    'intro' => 'Utilisez ce lien pour choisir un nouveau mot de passe.',
                    'details' => [
                        'Lien' => $url,
                        'Validite' => '60 minutes',
                    ],
                    'actionUrl' => $url,
                    'actionText' => 'Modifier mon mot de passe',
                    'closing' => 'Si vous n avez pas demande cette action, ignorez ce message.',
                ]);
        });
    }
}
