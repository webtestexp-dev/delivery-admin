<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['delivery_id', 'product_id', 'product_name', 'unit', 'ordered_quantity', 'delivered_quantity', 'unit_price', 'line_total'])]
class DeliveryLine extends Model
{
    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:3',
            'delivered_quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(LedgerEntry::class);
    }
}
