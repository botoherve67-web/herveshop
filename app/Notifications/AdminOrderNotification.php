<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isProof = $this->event === 'preuve_paiement';
        $title = $isProof ? 'Nouvelle preuve de paiement' : 'Nouvelle commande';
        $details = [
            'Référence' => $this->order->reference,
            'Client' => $this->order->user?->name.' ('.$this->order->user?->email.')',
            'Montant' => number_format($this->order->total, 0, ',', ' ').' FCFA',
        ];

        if ($isProof) {
            $details['Transaction'] = $this->order->transaction_id;
            $details['Moyen de paiement'] = strtoupper((string) $this->order->moyen_paiement);
            $details['Statut du paiement'] = ucfirst(str_replace('_', ' ', (string) $this->order->statut_paiement));
        }

        return (new MailMessage)
            ->subject($title.' - '.$this->order->reference)
            ->view('emails.notification', [
                'title' => $title,
                'greeting' => 'Bonjour,',
                'intro' => $isProof ? 'Une nouvelle preuve de paiement a été envoyée par le client.' : 'Une nouvelle commande vient d’être créée.',
                'details' => $details,
                'actionUrl' => route('admin.orders.show', $this->order),
                'actionText' => 'Ouvrir la commande',
                'closing' => 'Connectez-vous à votre espace d’administration pour traiter cette demande.',
            ]);
    }
}