<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\RecurringOrder;
use App\Models\User;
use App\Services\DailyOrderGenerator;
use App\Services\DeliveryAccountingService;
use App\Services\PaymentRecorder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo records must not be seeded in production.');
        }

        $adminPassword = bin2hex("admin@123");
        $newAdminPassword = null;

        DB::transaction(function () use ($adminPassword, &$newAdminPassword): void {
            $admin = User::firstOrCreate(
                ['email' => 'admin@milkdelivery.test'],
                ['name' => 'Milk Delivery Admin', 'password' => $adminPassword],
            );

            if ($admin->wasRecentlyCreated) {
                $admin->forceFill(['role' => 'admin'])->save();
                $newAdminPassword = $adminPassword;
            } elseif ($admin->role !== 'admin') {
                throw new RuntimeException('admin@milkdelivery.test already exists but is not an admin.');
            }

            $freshDairy = Brand::firstOrCreate(
                ['name' => 'Fresh Dairy Demo'],
                ['description' => 'Sample milk and dairy brand', 'is_active' => true],
            );
            $dailyHarvest = Brand::firstOrCreate(
                ['name' => 'Daily Harvest Demo'],
                ['description' => 'Sample bakery and grocery brand', 'is_active' => true],
            );

            $wholeMilk = Product::firstOrCreate(
                ['brand_id' => $freshDairy->id, 'name' => 'Whole Milk 1L'],
                [
                    'unit' => 'packet',
                    'packet_size' => '1.000',
                    'packet_size_unit' => 'litre',
                    'price' => '35.00',
                    'is_active' => true,
                ],
            );
            $tonedMilk = Product::firstOrCreate(
                ['brand_id' => $freshDairy->id, 'name' => 'Toned Milk 500ml'],
                [
                    'unit' => 'packet',
                    'packet_size' => '0.500',
                    'packet_size_unit' => 'litre',
                    'price' => '22.00',
                    'is_active' => true,
                ],
            );
            $paneer = Product::firstOrCreate(
                ['brand_id' => $freshDairy->id, 'name' => 'Fresh Paneer'],
                [
                    'unit' => 'kg',
                    'packet_size' => null,
                    'packet_size_unit' => null,
                    'price' => '340.00',
                    'is_active' => true,
                ],
            );
            $bread = Product::firstOrCreate(
                ['brand_id' => $dailyHarvest->id, 'name' => 'Whole Wheat Bread'],
                [
                    'unit' => 'loaf',
                    'packet_size' => null,
                    'packet_size_unit' => null,
                    'price' => '40.00',
                    'is_active' => true,
                ],
            );

            $asha = Customer::firstOrCreate(
                ['phone' => '9000000101'],
                [
                    'name' => 'Asha Patel',
                    'address' => '12 Sample Market Road',
                    'business_name' => null,
                    'notes' => 'Demo customer record',
                    'is_active' => true,
                ],
            );
            $ravi = Customer::firstOrCreate(
                ['phone' => '9000000102'],
                [
                    'name' => 'Ravi Sharma',
                    'address' => '8 Example Park Avenue',
                    'business_name' => null,
                    'notes' => 'Demo customer record',
                    'is_active' => true,
                ],
            );
            $greenBasket = Customer::firstOrCreate(
                ['phone' => '9000000103'],
                [
                    'name' => 'Green Basket Cafe',
                    'address' => '25 Demo High Street',
                    'business_name' => 'Green Basket Cafe',
                    'notes' => 'Demo business customer record',
                    'is_active' => true,
                ],
            );

            $asha->productPrices()->firstOrCreate(
                ['product_id' => $wholeMilk->id],
                ['price' => '32.50'],
            );

            $this->createRecurringOrder($asha, [
                [$wholeMilk, '2.000', null],
                [$paneer, '0.500', null],
            ]);
            $this->createRecurringOrder($ravi, [
                [$tonedMilk, '2.000', null],
                [$bread, '1.000', null],
            ]);
            $this->createRecurringOrder($greenBasket, [
                [$wholeMilk, '12.000', '32.50'],
                [$paneer, '2.000', null],
            ]);

            $today = Carbon::today();
            app(DailyOrderGenerator::class)->generate($today);

            $this->markDeliveryDelivered($asha, $today, $admin);
            $this->markDeliveryDelivered($greenBasket, $today, $admin);

            LedgerEntry::firstOrCreate(
                [
                    'customer_id' => $asha->id,
                    'entry_type' => 'opening_balance',
                    'description' => 'Demo opening balance',
                ],
                [
                    'side' => 'debit',
                    'amount' => '25.00',
                    'transaction_at' => $today->copy()->subDays(7)->startOfDay(),
                    'recorded_by' => $admin->id,
                ],
            );

            app(PaymentRecorder::class)->record([
                'customer_id' => $asha->id,
                'amount' => '50.00',
                'method' => 'google_pay',
                'paid_at' => $today->copy()->setTime(10, 30),
                'reference_number' => 'DEMO-GPAY-001',
                'idempotency_key' => '00000000-0000-4000-8000-000000000101',
                'notes' => 'Demo partial payment',
            ], $admin->id);
        });

        if ($newAdminPassword !== null) {
            $this->command?->info('Created admin account: admin@milkdelivery.test');
            $this->command?->warn("One-time password (save it now): {$newAdminPassword}");
        } else {
            $this->command?->info('Admin account already exists; its password was not changed.');
        }

        $this->command?->info('Demo brands, products, customers, recurring orders, deliveries, and ledger records are ready.');
    }

    /**
     * @param  array<int, array{0: Product, 1: string, 2: string|null}>  $lines
     */
    private function createRecurringOrder(Customer $customer, array $lines): void
    {
        $order = RecurringOrder::firstOrCreate(
            ['customer_id' => $customer->id, 'notes' => 'Demo daily recurring order'],
            [
                'starts_on' => Carbon::parse('2026-01-01'),
                'ends_on' => null,
                'is_active' => true,
            ],
        );

        foreach ($lines as [$product, $quantity, $unitPrice]) {
            $order->lines()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $quantity, 'unit_price' => $unitPrice],
            );
        }
    }

    private function markDeliveryDelivered(Customer $customer, Carbon $date, User $admin): void
    {
        $delivery = Delivery::where('customer_id', $customer->id)
            ->whereDate('delivery_date', $date)
            ->firstOrFail();

        $delivery->update(['status' => 'delivered']);

        foreach ($delivery->lines as $line) {
            $unitPrice = $line->unit_price;
            $customerPrice = $customer->productPrices()
                ->where('product_id', $line->product_id)
                ->value('price');

            if ($customerPrice !== null) {
                $unitPrice = $customerPrice;
            }

            $lineTotal = number_format(
                (int) round((float) $line->ordered_quantity * (float) $unitPrice * 100) / 100,
                2,
                '.',
                '',
            );

            $line->update([
                'delivered_quantity' => $line->ordered_quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        app(DeliveryAccountingService::class)->synchronize($delivery, $admin->id);
    }
}
