<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Services\OutstandingBalance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->withCount('deliveries')
            ->when($request->string('q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn ($search) => $search->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('business_name', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.form', [
            'customer' => new Customer,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $customer = DB::transaction(function () use ($data) {
            $openingBalance = $data['opening_balance'] ?? 0;
            $customer = Customer::create(collect($data)->except(['product_prices', 'opening_balance'])->all());
            $this->syncPrices($customer, $data['product_prices'] ?? []);

            if ((float) $openingBalance > 0) {
                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'entry_type' => 'opening_balance',
                    'side' => 'debit',
                    'amount' => $openingBalance,
                    'transaction_at' => now(),
                    'recorded_by' => auth()->id(),
                    'description' => 'Opening customer balance',
                ]);
            }

            return $customer;
        });

        return redirect()->route('customers.show', $customer)->with('status', 'Customer created successfully.');
    }

    public function show(Customer $customer, OutstandingBalance $balance): View
    {
        $customer->load([
            'deliveries' => fn ($query) => $query->withCount('lines')->orderByDesc('delivery_date')->limit(10),
            'payments' => fn ($query) => $query->orderByDesc('paid_at')->limit(10),
            'productPrices.product',
            'ledgerEntries' => fn ($query) => $query->orderByDesc('transaction_at')->limit(20),
            'invoices' => fn ($query) => $query->orderByDesc('period_start')->limit(10),
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'balance' => $balance->forCustomer($customer->id),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $customer->load('productPrices');

        return view('customers.form', [
            'customer' => $customer,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer);

        DB::transaction(function () use ($customer, $data) {
            $customer->update(collect($data)->except('product_prices')->all());
            $this->syncPrices($customer, $data['product_prices'] ?? []);
        });

        return redirect()->route('customers.show', $customer)->with('status', 'Customer updated successfully.');
    }

    public function setStatus(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $customer->update($data);

        return redirect()->route('customers.index')->with('status', $customer->is_active ? 'Customer activated.' : 'Customer deactivated.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:5000'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'opening_balance' => $customer
                ? ['prohibited']
                : ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'product_prices' => ['nullable', 'array'],
            'product_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ]);

        foreach (array_keys($data['product_prices'] ?? []) as $productId) {
            if (! ctype_digit((string) $productId) || ! Product::whereKey($productId)->exists()) {
                throw ValidationException::withMessages(["product_prices.{$productId}" => 'The selected product is invalid.']);
            }
        }

        return $data;
    }

    private function syncPrices(Customer $customer, array $prices): void
    {
        $customer->productPrices()->delete();

        foreach ($prices as $productId => $price) {
            if ($price !== null && $price !== '') {
                $customer->productPrices()->create([
                    'product_id' => Product::whereKey($productId)->value('id'),
                    'price' => $price,
                ]);
            }
        }
    }
}
