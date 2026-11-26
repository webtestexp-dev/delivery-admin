<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\User;
use App\Services\DailyOrderGenerator;
use App\Services\DeliveryAccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DailyDeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:pending,delivered,cancelled'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);
        $date = $filters['date'] ?? today()->toDateString();
        $deliveries = Delivery::with('customer', 'deliveryPerson', 'lines')
            ->whereDate('delivery_date', $date)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->whereHas('customer', fn ($customer) => $customer
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%')))
            ->orderBy('status')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return view('deliveries.index', compact('deliveries', 'date'));
    }

    public function generate(Request $request, DailyOrderGenerator $generator): RedirectResponse
    {
        $data = $request->validate(['date' => ['required', 'date']]);
        $generated = $generator->generate(Carbon::parse($data['date']));

        return redirect()->route('deliveries.index', ['date' => $data['date']])
            ->with('status', "{$generated} delivery line(s) generated. Existing daily orders were preserved.");
    }

    public function create(): View
    {
        return view('deliveries.create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'products' => \App\Models\Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => [
                'required',
                'exists:customers,id',
                Rule::unique('deliveries', 'customer_id')
                    ->where(fn ($query) => $query->where('delivery_date', $request->input('delivery_date'))),
            ],
            'delivery_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'lines.*.ordered_quantity' => ['required', 'numeric', 'gt:0', 'max:9999999.999'],
        ]);

        $delivery = DB::transaction(function () use ($data) {
            $delivery = Delivery::create([
                'customer_id' => $data['customer_id'],
                'delivery_date' => $data['delivery_date'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $entry) {
                $product = \App\Models\Product::findOrFail($entry['product_id']);
                $price = \App\Models\CustomerProductPrice::where('customer_id', $delivery->customer_id)
                    ->where('product_id', $product->id)
                    ->value('price') ?? $product->price;
                $delivery->lines()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'ordered_quantity' => $entry['ordered_quantity'],
                    'delivered_quantity' => 0,
                    'unit_price' => $price,
                    'line_total' => 0,
                ]);
            }

            return $delivery;
        });

        return redirect()->route('deliveries.edit', $delivery)->with('status', 'Daily order created.');
    }

    public function edit(Delivery $delivery): View
    {
        $delivery->load('customer', 'deliveryPerson', 'lines');

        return view('deliveries.edit', [
            'delivery' => $delivery,
            'deliveryPeople' => User::where('role', 'delivery_person')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Delivery $delivery, DeliveryAccountingService $accounting): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,delivered,cancelled'],
            'delivery_person_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'delivery_person')],
            'notes' => ['nullable', 'string', 'max:5000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'integer', 'distinct'],
            'lines.*.ordered_quantity' => ['required', 'numeric', 'gt:0', 'max:9999999.999'],
            'lines.*.delivered_quantity' => ['required', 'numeric', 'min:0', 'max:9999999.999'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ]);

        DB::transaction(function () use ($data, $delivery, $accounting) {
            $lockedDelivery = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            $lineIds = collect($data['lines'])->pluck('id')->all();
            $actualIds = $lockedDelivery->lines()->pluck('id')->all();

            if (array_diff($actualIds, $lineIds) || array_diff($lineIds, $actualIds)) {
                throw ValidationException::withMessages(['lines' => 'The submitted delivery lines do not match this order.']);
            }

            $lockedDelivery->update([
                'status' => $data['status'],
                'delivery_person_id' => $data['delivery_person_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $index => $entry) {
                $line = DeliveryLine::whereKey($entry['id'])->lockForUpdate()->firstOrFail();
                $totalCents = (int) round((float) $entry['delivered_quantity'] * (float) $entry['unit_price'] * 100);
                if ($totalCents > 999999999999) {
                    throw ValidationException::withMessages(["lines.{$index}.delivered_quantity" => 'The delivery line total exceeds the supported amount.']);
                }
                $total = number_format($totalCents / 100, 2, '.', '');
                $line->update([
                    'ordered_quantity' => $entry['ordered_quantity'],
                    'delivered_quantity' => $entry['delivered_quantity'],
                    'unit_price' => $entry['unit_price'],
                    'line_total' => $total,
                ]);
            }

            $accounting->synchronize($lockedDelivery, auth()->id());
        });

        return redirect()->route('deliveries.index', ['date' => $delivery->delivery_date->toDateString()])
            ->with('status', 'Delivery saved. Financial changes were recorded in the ledger.');
    }
}
