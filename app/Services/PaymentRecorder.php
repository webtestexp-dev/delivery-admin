<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentRecorder
{
    public function record(array $data, ?int $recordedBy): Payment
    {
        return DB::transaction(function () use ($data, $recordedBy) {
            Customer::whereKey($data['customer_id'])->lockForUpdate()->firstOrFail();
            $existing = Payment::where('idempotency_key', $data['idempotency_key'])->first();

            if ($existing) {
                $samePayment = $existing->customer_id === (int) $data['customer_id']
                    && (int) round((float) $existing->amount * 100) === (int) round((float) $data['amount'] * 100)
                    && $existing->method === $data['method']
                    && $existing->reference_number === ($data['reference_number'] ?? null);

                if (! $samePayment) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This request key has already been used for a different payment.',
                    ]);
                }

                return $existing;
            }

            $payment = Payment::create($data + ['recorded_by' => $recordedBy]);
            LedgerEntry::create([
                'customer_id' => $payment->customer_id,
                'payment_id' => $payment->id,
                'recorded_by' => $recordedBy,
                'entry_type' => 'payment',
                'side' => 'credit',
                'amount' => $payment->amount,
                'transaction_at' => $payment->paid_at,
                'description' => 'Customer payment',
            ]);

            return $payment;
        });
    }
}
