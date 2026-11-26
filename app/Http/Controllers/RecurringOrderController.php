<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\RecurringOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecurringOrderController extends Controller
{
    public function index(): View
    {
        return view('recurring-orders.index', [
            'orders' => RecurringOrder::with('customer', 'lines.product')->orderByDesc('is_active')->orderBy('customer_id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('recurring-orders.form', [
            'order' => new RecurringOrder,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data) {
            $lines = $data['lines'];
            $order = RecurringOrder::create(collect($data)->except('lines')->all());
            $order->lines()->createMany($lines);

            return $order;
        });

        return redirect()->route('recurring-orders.index')->with('status', 'Recurring order created.');
    }

    public function edit(RecurringOrder $recurringOrder): View
    {
        return view('recurring-orders.form', [
            'order' => $recurringOrder->load('lines'),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, RecurringOrder $recurringOrder): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($recurringOrder, $data) {
            $recurringOrder->update(collect($data)->except('lines')->all());
            $recurringOrder->lines()->delete();
            $recurringOrder->lines()->createMany($data['lines']);
        });

        return redirect()->route('recurring-orders.index')->with('status', 'Recurring order updated. Existing delivery records were not changed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'max:9999999.999'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ]);
    }
}
