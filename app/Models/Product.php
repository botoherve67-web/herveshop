<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'stock',
        'type', 'acompte_pourcent', 'date_cloture_precommande', 'date_expedition_prevue', 'date_arrivage_estimee',
        'bascule_auto_precommande', 'is_active',
    ];

    protected $casts = [
        'bascule_auto_precommande' => 'boolean',
        'is_active' => 'boolean',
        'date_cloture_precommande' => 'date',
        'date_expedition_prevue' => 'date',
        'date_arrivage_estimee' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('position');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function noteMoyenne(): float
    {
        return round($this->reviews()->avg('note') ?? 0, 1);
    }

    public function estEnPrecommande(): bool
    {
        if ($this->type === 'precommande') {
            return true;
        }

        return $this->stock <= 0 && $this->bascule_auto_precommande;
    }

    public function montantAcompte(): int
    {
        return (int) round($this->price * $this->acompte_pourcent / 100);
    }

    public function montantSolde(): int
    {
        return $this->price - $this->montantAcompte();
    }
}
