<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'position', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function url(): string
    {
        return Storage::disk('media')->url($this->path);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
