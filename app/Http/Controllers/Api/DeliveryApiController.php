<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\Payment;
use App\Services\DeliveryAccountingService;
use App\Services\PaymentRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryApiController extends Controller
{
    public function today(Request $request): JsonResponse
    {
        $deliveries = Delivery::with('customer:id,name,phone,address', 'lines:id,delivery_id,product_id,product_name,unit,ordered_quantity,delivered_quantity,unit_price')
            ->where('delivery_person_id', $request->user()->id)
            ->whereDate('delivery_date', today())
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $deliveries]);
    }

    public function update(Request $request, Delivery $delivery, DeliveryAccountingService $accounting): JsonResponse
    {
        abort_unless($delivery->delivery_person_id === $request->user()->id, 404);
        $data = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'integer', 'distinct'],
            'lines.*.delivered_quantity' => ['required', 'numeric', 'min:0', 'max:9999999.999'],
        ]);

        DB::transaction(function () use ($data, $delivery, $accounting, $request) {
            $delivery = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_unless($delivery->delivery_person_id === $request->user()->id, 404);
            $lineIds = collect($data['lines'])->pluck('id')->all();
            $actualIds = $delivery->lines()->pluck('id')->all();
            if (array_diff($actualIds, $lineIds) || array_diff($lineIds, $actualIds)) {
                throw ValidationException::withMessages(['lines' => 'The submitted delivery lines do not match this order.']);
            }

            foreach ($data['lines'] as $index => $entry) {
                $line = DeliveryLine::whereKey($entry['id'])->firstOrFail();
                $totalCents = (int) round((float) $entry['delivered_quantity'] * (float) $line->unit_price * 100);
                if ($totalCents > 999999999999) {
                    throw ValidationException::withMessages(["lines.{$index}.delivered_quantity" => 'The delivery line total exceeds the supported amount.']);
                }
                $total = number_format($totalCents / 100, 2, '.', '');
                $line->update(['delivered_quantity' => $entry['delivered_quantity'], 'line_total' => $total]);
            }
            $delivery->update(['status' => 'delivered']);
            $accounting->synchronize($delivery, $request->user()->id);
        });

        return response()->json(['message' => 'Delivery submitted.']);
    }

    public function recordPayment(Request $request, Delivery $delivery, PaymentRecorder $recorder): JsonResponse
    {
        abort_unless($delivery->delivery_person_id === $request->user()->id, 404);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'method' => ['required', 'in:cash,google_pay,paytm'],
            'paid_at' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'uuid'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $payment = $recorder->record([
            ...$data,
            'customer_id' => $delivery->customer_id,
            'paid_at' => $data['paid_at'] ?? now(),
        ], $request->user()->id);

        return response()->json(['data' => $payment], 201);
    }
}
