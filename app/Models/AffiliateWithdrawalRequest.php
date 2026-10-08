<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawalRequest extends Model
{
    protected $fillable = [
        'partner_id',
        'amount',
        'payment_method',
        'payment_number',
        'status',
        'reviewed_by',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
