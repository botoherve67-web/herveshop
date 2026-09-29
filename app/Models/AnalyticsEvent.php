<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = ['type', 'path', 'target', 'visitor_hash', 'user_id', 'ip_hash'];
}
