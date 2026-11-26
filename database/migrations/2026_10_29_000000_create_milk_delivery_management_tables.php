<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('unit', 40);
            $table->decimal('packet_size', 10, 3)->nullable();
            $table->string('packet_size_unit', 20)->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->text('address');
            $table->string('business_name')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name']);
            $table->index('phone');
        });

        Schema::create('customer_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->unique(['customer_id', 'product_id']);
        });

        Schema::create('recurring_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'is_active']);
        });

        Schema::create('recurring_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['recurring_order_id', 'product_id']);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('delivery_date');
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'delivery_date']);
            $table->index(['delivery_date', 'status']);
        });

        Schema::create('delivery_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('unit', 40);
            $table->decimal('ordered_quantity', 10, 3);
            $table->decimal('delivered_quantity', 10, 3)->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['delivery_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'google_pay', 'paytm']);
            $table->dateTime('paid_at');
            $table->string('reference_number')->nullable();
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['paid_at', 'method']);
            $table->index(['customer_id', 'paid_at']);
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('delivery_line_id')->nullable()->constrained()->restrictOnDelete()->unique();
            $table->foreignId('payment_id')->nullable()->constrained()->restrictOnDelete()->unique();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('entry_type', ['opening_balance', 'delivery_charge', 'payment', 'adjustment']);
            $table->enum('side', ['debit', 'credit']);
            $table->decimal('amount', 12, 2);
            $table->dateTime('transaction_at');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'transaction_at']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->enum('period_type', ['daily', 'weekly', 'monthly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('payments_total', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->dateTime('issued_at');
            $table->timestamps();
            $table->unique(['customer_id', 'period_type', 'period_start', 'period_end'], 'invoices_customer_period_unique');
            $table->index(['period_start', 'period_end']);
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_line_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('unit', 40);
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            $table->unique(['invoice_id', 'delivery_line_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('delivery_lines');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('recurring_order_lines');
        Schema::dropIfExists('recurring_orders');
        Schema::dropIfExists('customer_product_prices');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
    }
};
