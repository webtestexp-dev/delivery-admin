<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invoice_id', 'delivery_line_id', 'product_name', 'unit', 'quantity', 'unit_price', 'line_total'])]
class InvoiceLine extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deliveryLine(): BelongsTo
    {
        return $this->belongsTo(DeliveryLine::class);
    }
}
