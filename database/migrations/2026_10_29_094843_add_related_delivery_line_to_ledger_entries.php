<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('related_delivery_line_id')->nullable()->after('delivery_line_id')->constrained('delivery_lines')->restrictOnDelete();
            $table->index(['related_delivery_line_id', 'transaction_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropIndex(['related_delivery_line_id', 'transaction_at']);
            $table->dropConstrainedForeignId('related_delivery_line_id');
        });
    }
};
