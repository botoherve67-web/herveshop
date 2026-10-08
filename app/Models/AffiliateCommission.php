<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $fillable = [
        'partner_id',
        'order_id',
        'eligible_amount',
        'commission_amount',
        'status',
        'reversed_at',
    ];

    protected $casts = [
        'reversed_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
