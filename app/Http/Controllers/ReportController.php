<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const REPORTS = ['daily-sales', 'customer-outstanding', 'payment-collection', 'product-sales'];

    public function index(Request $request): View|StreamedResponse
    {
        $filters = $request->validate([
            'report' => ['nullable', 'in:'.implode(',', self::REPORTS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'export' => ['nullable', 'in:csv'],
        ]);
        $report = $filters['report'] ?? 'daily-sales';
        $from = $filters['from'] ?? today()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();
        $rows = $this->rows($report, $from, $to);

        if (($filters['export'] ?? null) === 'csv') {
            return Response::streamDownload(function () use ($rows, $report) {
                $stream = fopen('php://output', 'w');
                fputcsv($stream, $this->headers($report));
                foreach ($rows as $row) {
                    fputcsv($stream, array_map(function ($value) {
                        $value = (string) $value;

                        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
                    }, $row));
                }
                fclose($stream);
            }, $report.'-'.$from.'-'.$to.'.csv', ['Content-Type' => 'text/csv']);
        }

        return view('reports.index', compact('report', 'from', 'to', 'rows'));
    }

    private function rows(string $report, string $from, string $to): array
    {
        if ($report === 'customer-outstanding') {
            return Customer::query()
                ->leftJoin('ledger_entries', 'customers.id', '=', 'ledger_entries.customer_id')
                ->where(function ($query) use ($to) {
                    $query->whereNull('ledger_entries.transaction_at')
                        ->orWhere('ledger_entries.transaction_at', '<=', Carbon::parse($to)->endOfDay());
                })
                ->select('customers.name', 'customers.phone')
                ->selectRaw("COALESCE(SUM(CASE WHEN ledger_entries.side = 'debit' THEN ledger_entries.amount ELSE -ledger_entries.amount END), 0) as balance")
                ->groupBy('customers.id', 'customers.name', 'customers.phone')
                ->orderByDesc('balance')
                ->get()
                ->map(fn ($row) => [$row->name, $row->phone, number_format((float) $row->balance, 2, '.', '')])
                ->all();
        }

        if ($report === 'payment-collection') {
            return Payment::query()
                ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
                ->select('method')
                ->selectRaw('COUNT(*) as payment_count, SUM(amount) as total')
                ->groupBy('method')
                ->orderBy('method')
                ->get()
                ->map(fn ($row) => [$row->method, (int) $row->payment_count, number_format((float) $row->total, 2, '.', '')])
                ->all();
        }

        if ($report === 'product-sales') {
            $totals = [];
            foreach ($this->financialEntries($from, $to) as $entry) {
                $metadata = $entry->metadata ?? [];
                $name = $metadata['product_name'] ?? 'Unspecified product';
                $unit = $metadata['unit'] ?? 'unit';
                $key = $name.'|'.$unit;
                $totals[$key] ??= ['name' => $name, 'unit' => $unit, 'quantity' => 0.0, 'amount' => 0.0];
                $totals[$key]['amount'] += $entry->side === 'debit' ? (float) $entry->amount : -(float) $entry->amount;
                if ($entry->entry_type === 'delivery_charge') {
                    $totals[$key]['quantity'] += (float) ($metadata['quantity'] ?? 0);
                }
            }

            return collect($totals)->sortBy('name')->map(fn ($row) => [
                $row['name'],
                $row['unit'],
                number_format($row['quantity'], 3, '.', ''),
                number_format($row['amount'], 2, '.', ''),
            ])->values()->all();
        }

        $totals = [];
        foreach ($this->financialEntries($from, $to) as $entry) {
            $date = $entry->transaction_at->toDateString();
            $totals[$date] ??= ['deliveries' => [], 'amount' => 0.0];
            $totals[$date]['amount'] += $entry->side === 'debit' ? (float) $entry->amount : -(float) $entry->amount;
            if ($entry->entry_type === 'delivery_charge' && isset($entry->metadata['delivery_id'])) {
                $totals[$date]['deliveries'][$entry->metadata['delivery_id']] = true;
            }
        }

        ksort($totals);

        return collect($totals)->map(fn ($row, $date) => [
            $date,
            count($row['deliveries']),
            number_format($row['amount'], 2, '.', ''),
        ])->values()->all();
    }

    private function financialEntries(string $from, string $to)
    {
        return LedgerEntry::whereIn('entry_type', ['delivery_charge', 'adjustment'])
            ->whereBetween('transaction_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->orderBy('transaction_at')
            ->get();
    }

    private function headers(string $report): array
    {
        return match ($report) {
            'customer-outstanding' => ['Customer', 'Phone', 'Outstanding balance'],
            'payment-collection' => ['Method', 'Payments', 'Amount'],
            'product-sales' => ['Product', 'Unit', 'Quantity', 'Sales'],
            default => ['Date', 'Deliveries', 'Sales'],
        };
    }
}
