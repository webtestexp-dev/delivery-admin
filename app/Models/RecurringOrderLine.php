<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recurring_order_id', 'product_id', 'quantity', 'unit_price'])]
class RecurringOrderLine extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
        ];
    }

    public function recurringOrder(): BelongsTo
    {
        return $this->belongsTo(RecurringOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
