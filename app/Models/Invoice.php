<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'invoice_number', 'customer_name', 'customer_business_name', 'customer_phone', 'customer_address', 'period_type', 'period_start', 'period_end', 'opening_balance', 'subtotal', 'payments_total', 'closing_balance', 'issued_at'])]
class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'payments_total' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
