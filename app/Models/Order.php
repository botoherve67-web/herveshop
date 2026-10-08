<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    private const STATUS_LABELS = [
        'en_attente' => 'En attente de confirmation',
        'confirmee' => 'Commande confirmée',
        'en_preparation' => 'En préparation',
        'expediee' => 'Expédiée',
        'livree' => 'Livrée',
        'annulee' => 'Annulée',
    ];

    private const PAYMENT_STATUS_LABELS = [
        'en_attente' => 'Paiement à confirmer',
        'acompte_paye' => 'Acompte payé',
        'paye' => 'Paiement confirmé',
        'echec' => 'Paiement refusé',
    ];

    protected $fillable = [
        'reference', 'tracking_code', 'user_id', 'type', 'mode_livraison', 'zone_livraison',
        'frais_livraison', 'adresse_livraison', 'point_retrait',
        'sous_total', 'reduction', 'code_promo', 'total',
        'montant_acompte', 'montant_solde', 'solde_echeance_at',
        'moyen_paiement', 'transaction_id', 'preuve_paiement_path',
        'preuve_paiement_envoyee_at', 'statut_paiement', 'statut', 'remarque_admin',
        'affiliate_partner_id', 'courier_id',
    ];

    protected $casts = [
        'solde_echeance_at' => 'datetime',
        'preuve_paiement_envoyee_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->with('user')->latest();
    }

    public function affiliatePartner()
    {
        return $this->belongsTo(User::class, 'affiliate_partner_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function affiliateCommission()
    {
        return $this->hasOne(AffiliateCommission::class);
    }

    public function statusLabel(): string
    {
        return self::statusLabelFor($this->statut);
    }

    public function paymentStatusLabel(): string
    {
        return self::paymentStatusLabelFor($this->statut_paiement);
    }

    public static function statusLabelFor(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    public static function paymentStatusLabelFor(?string $status): string
    {
        return self::PAYMENT_STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    public function statusDescription(): string
    {
        return match ($this->statut) {
            'en_attente' => 'Nous avons reçu votre commande. Elle attend la confirmation de notre équipe.',
            'confirmee' => 'Votre commande est confirmée et va être préparée.',
            'en_preparation' => 'Nous préparons vos articles avant leur expédition ou leur mise à disposition.',
            'expediee' => 'Votre commande a été expédiée. Consultez le code de suivi ci-dessous, s’il est disponible.',
            'livree' => 'Votre commande a été livrée.',
            'annulee' => 'Cette commande a été annulée. Consultez le message de notre équipe ci-dessous ou contactez le service client.',
            default => 'Consultez cette page pour voir les dernières informations sur votre commande.',
        };
    }

    public static function historyTypeLabel(string $type): string
    {
        return match ($type) {
            'commande' => 'Commande',
            'paiement' => 'Paiement',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
