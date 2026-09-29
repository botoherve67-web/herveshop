<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'note', 'commentaire', 'is_approved',
        'report_count', 'report_reason', 'reported_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'reported_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
