<?php

namespace App\Notifications;

use App\Models\AffiliateWithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AffiliateWithdrawalNotification extends Notification
{
    use Queueable;

    public function __construct(public AffiliateWithdrawalRequest $withdrawal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $partner = $this->withdrawal->partner;

        return (new MailMessage)
            ->subject('Demande de retrait partenaire à vérifier')
            ->view('emails.notification', [
                'title' => 'Demande de retrait partenaire',
                'greeting' => 'Bonjour,',
                'intro' => 'Un partenaire a demandé le retrait de son solde. Vérifiez le montant disponible avant validation.',
                'details' => [
                    'Partenaire' => $partner?->name.' ('.$partner?->email.')',
                    'Montant demandé' => number_format($this->withdrawal->amount, 0, ',', ' ').' FCFA',
                    'Moyen de paiement' => strtoupper($this->withdrawal->payment_method),
                    'Numéro de paiement' => $this->withdrawal->payment_number,
                ],
                'actionUrl' => route('admin.affiliate-withdrawals.index'),
                'actionText' => 'Vérifier les retraits',
                'closing' => 'Connectez-vous à l’administration pour approuver ou rejeter cette demande.',
            ]);
    }
}
