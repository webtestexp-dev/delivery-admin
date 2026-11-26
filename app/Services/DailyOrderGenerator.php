<?php

namespace App\Services;

use App\Models\CustomerProductPrice;
use App\Models\Delivery;
use App\Models\RecurringOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyOrderGenerator
{
    public function generate(Carbon $date): int
    {
        return DB::transaction(function () use ($date) {
            $generated = 0;
            $dateValue = $date->toDateString();
            $orders = RecurringOrder::query()
                ->with('lines.product')
                ->where('is_active', true)
                ->whereDate('starts_on', '<=', $dateValue)
                ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $dateValue))
                ->whereHas('customer', fn ($query) => $query->where('is_active', true))
                ->get();

            foreach ($orders as $order) {
                $delivery = Delivery::where('customer_id', $order->customer_id)
                    ->whereDate('delivery_date', $dateValue)
                    ->first();

                if (! $delivery) {
                    Delivery::insertOrIgnore([
                        'customer_id' => $order->customer_id,
                        'delivery_date' => $dateValue,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $delivery = Delivery::where('customer_id', $order->customer_id)
                        ->whereDate('delivery_date', $dateValue)
                        ->firstOrFail();
                }

                foreach ($order->lines as $orderLine) {
                    $price = $orderLine->unit_price
                        ?? CustomerProductPrice::where('customer_id', $order->customer_id)
                            ->where('product_id', $orderLine->product_id)
                            ->value('price')
                        ?? $orderLine->product->price;

                    $line = $delivery->lines()->firstOrCreate(
                        ['product_id' => $orderLine->product_id],
                        [
                            'product_name' => $orderLine->product->name,
                            'unit' => $orderLine->product->unit,
                            'ordered_quantity' => $orderLine->quantity,
                            'delivered_quantity' => 0,
                            'unit_price' => $price,
                            'line_total' => 0,
                        ],
                    );

                    if ($line->wasRecentlyCreated) {
                        $generated++;
                    }
                }
            }

            return $generated;
        });
    }
}
