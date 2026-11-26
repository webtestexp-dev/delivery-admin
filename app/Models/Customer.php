<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'address', 'business_name', 'notes', 'is_active'])]
class Customer extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(CustomerProductPrice::class);
    }

    public function recurringOrders(): HasMany
    {
        return $this->hasMany(RecurringOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
