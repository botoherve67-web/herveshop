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
                'subject' => 'Commande enregistree - '.$this->order->reference,
                'intro' => 'Votre commande a bien ete enregistree.',
            ],
            'paiement' => [
                'subject' => 'Paiement mis a jour - '.$this->order->reference,
                'intro' => 'Le statut de votre paiement est maintenant : '.$this->label($this->order->statut_paiement).'.',
            ],
            'preuve_paiement' => [
                'subject' => 'Preuve de paiement recue - '.$this->order->reference,
                'intro' => 'Nous avons bien reçu votre preuve de paiement. Notre équipe va la vérifier et vous informera du résultat.',
            ],
            'expedition' => [
                'subject' => 'Commande expediee - '.$this->order->reference,
                'intro' => 'Votre commande est maintenant en cours d expedition.',
            ],
            'annulation' => [
                'subject' => 'Commande annulee - '.$this->order->reference,
                'intro' => 'Votre commande a ete annulee.',
            ],
            default => [
                'subject' => 'Statut commande mis a jour - '.$this->order->reference,
                'intro' => 'Le statut de votre commande est maintenant : '.$this->label($this->order->statut).'.',
            ],
        };
    }

    private function data(object $notifiable, array $content): array
    {
        $details = [
            'Reference' => $this->order->reference,
            'Statut commande' => $this->label($this->order->statut),
            'Statut paiement' => $this->label($this->order->statut_paiement),
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

    private function label(?string $value): string
    {
        return ucfirst(str_replace('_', ' ', (string) $value));
    }
}
