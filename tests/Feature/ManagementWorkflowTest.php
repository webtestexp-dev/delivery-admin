<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RecurringOrder;
use App\Models\User;
use App\Services\OutstandingBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManagementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_customer_crud_persists_specific_prices_and_deactivation(): void
    {
        $product = $this->product();
        $brand = $product->brand;

        $this->post(route('customers.store'), [
            'name' => 'Asha Patel',
            'phone' => '555-0101',
            'address' => '1 Market Street',
            'opening_balance' => '12.50',
            'product_prices' => [$product->id => '4.75'],
        ])->assertRedirect();

        $customer = Customer::where('phone', '555-0101')->firstOrFail();
        $this->get(route('customers.index'))->assertOk();
        $this->get(route('customers.show', $customer))->assertOk();
        $this->get(route('customers.edit', $customer))->assertOk();
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.edit', $product))->assertOk();
        $this->get(route('brands.index'))->assertOk();
        $this->patch(route('brands.status', $brand), ['is_active' => 0])
            ->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('customer_product_prices', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'price' => '4.75',
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'customer_id' => $customer->id,
            'entry_type' => 'opening_balance',
            'side' => 'debit',
            'amount' => '12.50',
        ]);

        $this->patch(route('customers.status', $customer), ['is_active' => 0])
            ->assertRedirect(route('customers.index'));
        $this->assertFalse($customer->fresh()->is_active);
    }

    public function test_daily_order_generation_is_idempotent_and_delivery_edits_append_ledger_adjustments(): void
    {
        $customer = Customer::create([
            'name' => 'Asha Patel',
            'phone' => '555-0101',
            'address' => '1 Market Street',
        ]);
        $product = $this->product();
        $order = RecurringOrder::create([
            'customer_id' => $customer->id,
            'starts_on' => Carbon::today()->subDays(5),
            'is_active' => true,
        ]);
        $order->lines()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '3.50',
        ]);

        $this->post(route('deliveries.generate'), ['date' => Carbon::today()->toDateString()])
            ->assertRedirect();
        $this->post(route('deliveries.store'), [
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::today()->toDateString(),
            'lines' => [['product_id' => $product->id, 'ordered_quantity' => 1]],
        ])->assertSessionHasErrors('customer_id');
        $this->post(route('deliveries.generate'), ['date' => Carbon::today()->toDateString()])
            ->assertRedirect();

        $delivery = Delivery::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame(1, $delivery->lines()->count());
        $this->get(route('deliveries.index', ['date' => Carbon::today()->toDateString()]))->assertOk();
        $this->get(route('deliveries.edit', $delivery))->assertOk();
        $this->get(route('recurring-orders.index'))->assertOk();
        $this->get(route('recurring-orders.edit', $order))->assertOk();
        $line = $delivery->lines()->firstOrFail();

        $this->put(route('deliveries.update', $delivery), [
            'status' => 'delivered',
            'lines' => [[
                'id' => $line->id,
                'ordered_quantity' => 2,
                'delivered_quantity' => 2,
                'unit_price' => '3.50',
            ]],
        ])->assertRedirect();

        $charge = LedgerEntry::where('delivery_line_id', $line->id)->firstOrFail();
        $this->assertSame('7.00', $charge->amount);
        $this->post(route('invoices.generate'), [
            'customer_id' => $customer->id,
            'period_type' => 'daily',
            'period_date' => Carbon::today()->toDateString(),
        ])->assertRedirect();
        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();

        $this->put(route('deliveries.update', $delivery), [
            'status' => 'delivered',
            'lines' => [[
                'id' => $line->id,
                'ordered_quantity' => 2,
                'delivered_quantity' => 2,
                'unit_price' => '4.00',
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('ledger_entries', [
            'delivery_line_id' => $line->id,
            'entry_type' => 'delivery_charge',
            'amount' => '7.00',
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'related_delivery_line_id' => $line->id,
            'entry_type' => 'adjustment',
            'side' => 'debit',
            'amount' => '1.00',
        ]);
        $this->assertSame('7.00', $invoice->lines()->firstOrFail()->line_total);

        $key = (string) Str::uuid();
        $payment = [
            'customer_id' => $customer->id,
            'amount' => '2.00',
            'method' => 'cash',
            'paid_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'idempotency_key' => $key,
        ];
        $this->post(route('payments.store'), $payment)->assertRedirect();
        $this->post(route('payments.store'), $payment)->assertRedirect();

        $this->assertSame(1, Payment::where('idempotency_key', $key)->count());
        $savedPayment = Payment::where('idempotency_key', $key)->firstOrFail();
        $this->get(route('payments.index'))->assertOk();
        $this->get(route('payments.receipt', $savedPayment))->assertOk();
        $this->assertSame('6.00', app(OutstandingBalance::class)->forCustomer($customer->id));
    }

    public function test_invoice_snapshot_duplicate_prevention_and_csv_reports(): void
    {
        $customer = Customer::create([
            'name' => 'Asha Patel',
            'phone' => '555-0101',
            'address' => '1 Market Street',
        ]);
        $product = $this->product();
        $delivery = Delivery::create([
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::today(),
            'status' => 'delivered',
        ]);
        DeliveryLine::create([
            'delivery_id' => $delivery->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'ordered_quantity' => 1,
            'delivered_quantity' => 1,
            'unit_price' => '3.50',
            'line_total' => '3.50',
        ]);
        LedgerEntry::create([
            'customer_id' => $customer->id,
            'delivery_line_id' => $delivery->lines()->first()->id,
            'entry_type' => 'delivery_charge',
            'side' => 'debit',
            'amount' => '3.50',
            'transaction_at' => Carbon::today()->startOfDay(),
        ]);

        $data = [
            'customer_id' => $customer->id,
            'period_type' => 'daily',
            'period_date' => Carbon::today()->toDateString(),
        ];
        $this->post(route('invoices.generate'), $data)->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'period_type' => 'daily',
            'subtotal' => '3.50',
            'closing_balance' => '3.50',
        ]);
        $this->post(route('invoices.generate'), $data)->assertSessionHasErrors('period_date');
        foreach (['weekly', 'monthly'] as $periodType) {
            $this->post(route('invoices.generate'), [
                'customer_id' => $customer->id,
                'period_type' => $periodType,
                'period_date' => Carbon::today()->toDateString(),
            ])->assertRedirect();
            $this->assertDatabaseHas('invoices', [
                'customer_id' => $customer->id,
                'period_type' => $periodType,
            ]);
        }
        $periodInvoices = Invoice::where('customer_id', $customer->id)->get();
        $filteredInvoices = $this->get(route('invoices.index', [
            'from' => Carbon::today()->toDateString(),
            'to' => Carbon::today()->toDateString(),
        ]))->assertOk();
        foreach ($periodInvoices as $periodInvoice) {
            $filteredInvoices->assertSee($periodInvoice->invoice_number);
        }

        $invoice = Invoice::firstOrFail();
        $customer->update(['address' => 'Changed after invoice']);
        $latePayment = Payment::create([
            'customer_id' => $customer->id,
            'amount' => '1.00',
            'method' => 'cash',
            'paid_at' => Carbon::now(),
            'reference_number' => 'late-backdated',
        ]);
        LedgerEntry::create([
            'customer_id' => $customer->id,
            'payment_id' => $latePayment->id,
            'entry_type' => 'payment',
            'side' => 'credit',
            'amount' => '1.00',
            'transaction_at' => Carbon::now(),
        ]);
        $this->get(route('invoices.index'))->assertOk();
        $this->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('1 Market Street')
            ->assertDontSee('Changed after invoice')
            ->assertDontSee('late-backdated');
        $this->assertSame(0, $invoice->payments()->count());
        $this->get(route('invoices.pdf', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('reports.index'))->assertOk();

        $this->get(route('reports.index', [
            'report' => 'daily-sales',
            'from' => Carbon::today()->toDateString(),
            'to' => Carbon::today()->toDateString(),
            'export' => 'csv',
        ]))->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');
        foreach (['customer-outstanding', 'payment-collection', 'product-sales'] as $report) {
            $this->get(route('reports.index', [
                'report' => $report,
                'from' => Carbon::today()->toDateString(),
                'to' => Carbon::today()->toDateString(),
            ]))->assertOk();
        }
    }

    public function test_android_api_requires_a_scoped_token_and_returns_only_assigned_deliveries(): void
    {
        $user = User::factory()->create();
        $user->role = 'delivery_person';
        $user->save();
        $customer = Customer::create([
            'name' => 'Asha Patel',
            'phone' => '555-0101',
            'address' => '1 Market Street',
        ]);
        $delivery = Delivery::create([
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::today(),
            'delivery_person_id' => $user->id,
            'status' => 'pending',
        ]);
        $product = $this->product();
        $line = $delivery->lines()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'ordered_quantity' => 1,
            'delivered_quantity' => 0,
            'unit_price' => '3.50',
            'line_total' => '0.00',
        ]);

        Auth::logout();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/deliveries/today')->assertUnauthorized();
        $tokenResponse = $this->postJson('/api/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
        ])->assertOk();

        $this->withToken($tokenResponse->json('token'))
            ->getJson('/api/deliveries/today')
            ->assertOk()
            ->assertJsonPath('data.0.id', $delivery->id)
            ->assertJsonPath('data.0.customer.name', 'Asha Patel');

        $this->patchJson('/api/deliveries/'.$delivery->id, [
            'lines' => [['id' => $line->id, 'delivered_quantity' => 1]],
        ])->assertOk();
        $this->assertDatabaseHas('ledger_entries', [
            'delivery_line_id' => $line->id,
            'amount' => '3.50',
        ]);

        $this->postJson('/api/deliveries/'.$delivery->id.'/payments', [
            'amount' => '1.00',
            'method' => 'google_pay',
            'idempotency_key' => (string) Str::uuid(),
        ])->assertCreated();
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'amount' => '1.00',
            'method' => 'google_pay',
        ]);
    }

    public function test_delivery_person_cannot_open_admin_routes(): void
    {
        $user = auth()->user();
        $user->role = 'delivery_person';
        $user->save();

        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_staff_account_command_creates_a_delivery_person_without_public_registration(): void
    {
        $this->artisan('staff:create')
            ->expectsQuestion('Name', 'Route Driver')
            ->expectsQuestion('Email', 'driver@example.test')
            ->expectsChoice('Role', 'delivery_person', ['admin', 'delivery_person'])
            ->expectsQuestion('Password (minimum 12 characters)', 'long-test-password')
            ->expectsQuestion('Confirm password', 'long-test-password')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'driver@example.test',
            'role' => 'delivery_person',
        ]);
        $this->assertFalse(Route::has('register'));
    }

    private function product(): Product
    {
        $brand = Brand::create(['name' => 'Fresh Farms']);

        return Product::create([
            'brand_id' => $brand->id,
            'name' => 'Whole milk',
            'unit' => 'packet',
            'packet_size' => 1,
            'packet_size_unit' => 'litre',
            'price' => '3.50',
        ]);
    }
}
