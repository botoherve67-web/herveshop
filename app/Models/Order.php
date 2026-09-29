<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference', 'tracking_code', 'user_id', 'type', 'mode_livraison', 'zone_livraison',
        'frais_livraison', 'adresse_livraison', 'point_retrait',
        'sous_total', 'reduction', 'code_promo', 'total',
        'montant_acompte', 'montant_solde', 'solde_echeance_at',
        'moyen_paiement', 'transaction_id', 'preuve_paiement_path',
        'preuve_paiement_envoyee_at', 'statut_paiement', 'statut', 'remarque_admin',
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
}
