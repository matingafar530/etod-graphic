<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintTemplate extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['allowed_formats' => 'array', 'is_active' => 'boolean'];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
