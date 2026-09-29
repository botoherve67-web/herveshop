<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'exception_class', 'message', 'level', 'method', 'url', 'user_id', 'ip_address', 'trace',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}