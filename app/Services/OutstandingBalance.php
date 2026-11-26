<?php

namespace App\Services;

use App\Models\LedgerEntry;

class OutstandingBalance
{
    public function forCustomer(int $customerId): string
    {
        $balance = LedgerEntry::where('customer_id', $customerId)
            ->selectRaw("COALESCE(SUM(CASE WHEN side = 'debit' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');

        return number_format((float) $balance, 2, '.', '');
    }

    public function total(): string
    {
        $balance = LedgerEntry::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN side = 'debit' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');

        return number_format((float) $balance, 2, '.', '');
    }
}
