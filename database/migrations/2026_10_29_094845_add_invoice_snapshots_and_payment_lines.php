<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('customer_name')->nullable();
            $table->string('customer_business_name')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->text('customer_address')->nullable();
        });

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('paid_at');
            $table->string('method', 30);
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['invoice_id', 'payment_id']);
            $table->index(['invoice_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_business_name',
                'customer_phone',
                'customer_address',
            ]);
        });
    }
};
