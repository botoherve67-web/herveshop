<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'type', 'valeur', 'usage_max', 'usage_actuel', 'commence_le', 'expire_le', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commence_le' => 'date',
        'expire_le' => 'date',
    ];

    public function estValide(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = today()->toDateString();

        $startDate = $this->getRawOriginal('commence_le');
        $endDate = $this->getRawOriginal('expire_le');

        if ($startDate && $startDate > $today) {
            return false;
        }

        if ($endDate && $endDate < $today) {
            return false;
        }

        if ($this->usage_max && $this->usage_actuel >= $this->usage_max) {
            return false;
        }

        return true;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_promo_code');
    }

    public function calculerReduction(int $sousTotal): int
    {
        if ($this->type === 'pourcentage') {
            return (int) round($sousTotal * $this->valeur / 100);
        }

        return min($this->valeur, $sousTotal);
    }
}
