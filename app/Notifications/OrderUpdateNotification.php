<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderUpdateNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $event,
        public ?string $remark = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $content = $this->content();
        $data = $this->data($notifiable, $content);

        return (new MailMessage)
            ->subject($content['subject'])
            ->view('emails.notification', $data)
            ->text('emails.notification-text', $data);
    }

    private function content(): array
    {
        return match ($this->event) {
            'commande' => [
                'subject' => 'Commande enregistrée - '.$this->order->reference,
                'intro' => 'Votre commande a bien été enregistrée. '.$this->order->statusDescription(),
            ],
            'paiement' => [
                'subject' => 'Paiement mis à jour - '.$this->order->reference,
                'intro' => 'Le statut de votre paiement est maintenant : '.$this->order->paymentStatusLabel().'.',
            ],
            'preuve_paiement' => [
                'subject' => 'Preuve de paiement reçue - '.$this->order->reference,
                'intro' => 'Nous avons bien reçu votre preuve de paiement. Notre équipe va la vérifier et vous informera du résultat.',
            ],
            'expedition' => [
                'subject' => 'Commande expédiée - '.$this->order->reference,
                'intro' => $this->order->statusDescription(),
            ],
            'annulation' => [
                'subject' => 'Commande annulée - '.$this->order->reference,
                'intro' => $this->order->statusDescription(),
            ],
            default => [
                'subject' => 'Commande mise à jour - '.$this->order->reference,
                'intro' => 'Le statut de votre commande est maintenant « '.$this->order->statusLabel().' ». '.$this->order->statusDescription(),
            ],
        };
    }

    private function data(object $notifiable, array $content): array
    {
        $details = [
            'Référence' => $this->order->reference,
            'Statut de la commande' => $this->order->statusLabel(),
            'Statut du paiement' => $this->order->paymentStatusLabel(),
            'Montant' => number_format($this->order->total, 0, ',', ' ').' FCFA',
        ];

        if ($this->order->tracking_code) {
            $details['Code de suivi'] = $this->order->tracking_code;
        }

        if ($this->remark) {
            $details['Message'] = $this->remark;
        }

        return [
            'title' => $content['subject'],
            'greeting' => 'Bonjour '.$notifiable->name.',',
            'intro' => $content['intro'],
            'details' => $details,
            'actionUrl' => route('orders.show', $this->order),
            'actionText' => 'Voir ma commande',
            'closing' => 'Merci de votre confiance.',
        ];
    }
}
