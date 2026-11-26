<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

class DeliveryAccountingService
{
    public function synchronize(Delivery $delivery, ?int $recordedBy): void
    {
        foreach ($delivery->lines()->lockForUpdate()->get() as $line) {
            $targetCents = $delivery->status === 'delivered'
                ? (int) round((float) $line->line_total * 100)
                : 0;
            $netAmount = DB::table('ledger_entries')
                ->where('customer_id', $delivery->customer_id)
                ->where(function ($query) use ($line) {
                    $query->where('delivery_line_id', $line->id)
                        ->orWhere('related_delivery_line_id', $line->id);
                })
                ->selectRaw("COALESCE(SUM(CASE WHEN side = 'debit' THEN amount ELSE -amount END), 0) as balance")
                ->value('balance');
            $currentCents = (int) round((float) $netAmount * 100);
            $differenceCents = $targetCents - $currentCents;

            if ($differenceCents === 0) {
                continue;
            }

            $hasOriginalCharge = LedgerEntry::where('delivery_line_id', $line->id)->exists();
            $entryType = $hasOriginalCharge ? 'adjustment' : 'delivery_charge';

            LedgerEntry::create([
                'customer_id' => $delivery->customer_id,
                'delivery_line_id' => $hasOriginalCharge ? null : $line->id,
                'related_delivery_line_id' => $hasOriginalCharge ? $line->id : null,
                'recorded_by' => $recordedBy,
                'entry_type' => $entryType,
                'side' => $differenceCents > 0 ? 'debit' : 'credit',
                'amount' => number_format(abs($differenceCents) / 100, 2, '.', ''),
                'transaction_at' => $entryType === 'delivery_charge'
                    ? $delivery->delivery_date->startOfDay()
                    : now(),
                'description' => $entryType === 'delivery_charge'
                    ? 'Delivered products charge'
                    : 'Delivery amount adjustment',
                'metadata' => [
                    'delivery_id' => $delivery->id,
                    'delivery_line_id' => $line->id,
                    'product_name' => $line->product_name,
                    'unit' => $line->unit,
                    'quantity' => $line->delivered_quantity,
                    'unit_price' => $line->unit_price,
                ],
            ]);
        }
    }
}
