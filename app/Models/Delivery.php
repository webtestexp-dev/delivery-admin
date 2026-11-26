<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'delivery_date', 'delivery_person_id', 'status', 'notes'])]
class Delivery extends Model
{
    protected function casts(): array
    {
        return ['delivery_date' => 'date'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryLine::class);
    }
}
