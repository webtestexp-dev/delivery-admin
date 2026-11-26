<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['brand_id', 'name', 'unit', 'packet_size', 'packet_size_unit', 'price', 'is_active'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'packet_size' => 'decimal:3',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function deliveryLines(): HasMany
    {
        return $this->hasMany(DeliveryLine::class);
    }
}
