<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'delivery_line_id', 'related_delivery_line_id', 'payment_id', 'recorded_by', 'entry_type', 'side', 'amount', 'transaction_at', 'description', 'metadata'])]
class LedgerEntry extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryLine(): BelongsTo
    {
        return $this->belongsTo(DeliveryLine::class);
    }

    public function relatedDeliveryLine(): BelongsTo
    {
        return $this->belongsTo(DeliveryLine::class, 'related_delivery_line_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
