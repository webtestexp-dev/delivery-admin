<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_database_backed_operational_totals(): void
    {
        $this->actingAs(User::factory()->create());

        $brand = Brand::create(['name' => 'Fresh Farms']);
        Brand::create(['name' => 'Retired Brand', 'is_active' => false]);
        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => 'Whole milk',
            'unit' => 'packet',
            'packet_size' => 1,
            'packet_size_unit' => 'litre',
            'price' => 3.50,
        ]);
        $customer = Customer::create([
            'name' => 'Asha Patel',
            'phone' => '555-0101',
            'address' => '1 Market Street',
        ]);
        $delivery = Delivery::create([
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::today(),
            'status' => 'delivered',
        ]);
        $line = DeliveryLine::create([
            'delivery_id' => $delivery->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'ordered_quantity' => 2,
            'delivered_quantity' => 2,
            'unit_price' => 3.50,
            'line_total' => 7.00,
        ]);
        $payment = Payment::create([
            'customer_id' => $customer->id,
            'amount' => 2.00,
            'method' => 'cash',
            'paid_at' => Carbon::now(),
        ]);
        LedgerEntry::create([
            'customer_id' => $customer->id,
            'delivery_line_id' => $line->id,
            'entry_type' => 'delivery_charge',
            'side' => 'debit',
            'amount' => 7.00,
            'transaction_at' => Carbon::now(),
        ]);
        LedgerEntry::create([
            'customer_id' => $customer->id,
            'payment_id' => $payment->id,
            'entry_type' => 'payment',
            'side' => 'credit',
            'amount' => 2.00,
            'transaction_at' => Carbon::now(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Asha Patel')
            ->assertSee('2 total brands · 1 active')
            ->assertSee('₹7.00')
            ->assertSee('₹2.00')
            ->assertSee('₹5.00');
    }
}
