<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $invoices = Invoice::with('customer')
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('period_end', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('period_start', '<=', $date))
            ->orderByDesc('period_start')
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', [
            'invoices' => $invoices,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('invoices.create', ['customers' => Customer::where('is_active', true)->orderBy('name')->get()]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'period_type' => ['required', 'in:daily,weekly,monthly'],
            'period_date' => ['required', 'date'],
        ]);
        $period = Carbon::parse($data['period_date']);
        match ($data['period_type']) {
            'daily' => $period,
            'weekly' => $period->startOfWeek(),
            'monthly' => $period->startOfMonth(),
        };
        $start = $period->copy()->startOfDay();
        $end = match ($data['period_type']) {
            'daily' => $period->copy()->endOfDay(),
            'weekly' => $period->copy()->endOfWeek(),
            'monthly' => $period->copy()->endOfMonth(),
        };

        $duplicate = Invoice::where('customer_id', $data['customer_id'])
            ->where('period_type', $data['period_type'])
            ->whereDate('period_start', $start)
            ->whereDate('period_end', $end)
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['period_date' => 'An invoice already exists for this customer and billing period.']);
        }

        $invoice = DB::transaction(function () use ($data, $start, $end) {
            $customer = Customer::whereKey($data['customer_id'])->lockForUpdate()->firstOrFail();
            $alreadyGenerated = Invoice::where('customer_id', $customer->id)
                ->where('period_type', $data['period_type'])
                ->whereDate('period_start', $start)
                ->whereDate('period_end', $end)
                ->exists();
            if ($alreadyGenerated) {
                throw ValidationException::withMessages(['period_date' => 'An invoice already exists for this customer and billing period.']);
            }

            $opening = LedgerEntry::where('customer_id', $customer->id)
                ->where(function ($query) use ($start, $end) {
                    $query->where('transaction_at', '<', $start)
                        ->orWhere(function ($openingBalance) use ($start, $end) {
                            $openingBalance->where('entry_type', 'opening_balance')
                                ->whereBetween('transaction_at', [$start, $end]);
                        });
                })
                ->selectRaw("COALESCE(SUM(CASE WHEN side = 'debit' THEN amount ELSE -amount END), 0) as balance")
                ->value('balance');
            $periodMovement = LedgerEntry::where('customer_id', $customer->id)
                ->whereBetween('transaction_at', [$start, $end])
                ->where('entry_type', '!=', 'opening_balance')
                ->selectRaw("COALESCE(SUM(CASE WHEN side = 'debit' THEN amount ELSE -amount END), 0) as balance")
                ->value('balance');
            $subtotal = LedgerEntry::where('customer_id', $customer->id)
                ->whereIn('entry_type', ['delivery_charge', 'adjustment'])
                ->whereBetween('transaction_at', [$start, $end])
                ->selectRaw("COALESCE(SUM(CASE WHEN side = 'debit' THEN amount ELSE -amount END), 0) as balance")
                ->value('balance');
            $paymentsTotal = Payment::where('customer_id', $customer->id)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount');
            $payments = Payment::where('customer_id', $customer->id)
                ->whereBetween('paid_at', [$start, $end])
                ->orderBy('paid_at')
                ->get();
            $closingBalance = number_format(
                ((int) round((float) $opening * 100) + (int) round((float) $periodMovement * 100)) / 100,
                2,
                '.',
                '',
            );

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-'.$start->format('Ymd').'-'.$customer->id.'-'.Str::upper(Str::random(5)),
                'customer_name' => $customer->name,
                'customer_business_name' => $customer->business_name,
                'customer_phone' => $customer->phone,
                'customer_address' => $customer->address,
                'period_type' => $data['period_type'],
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'opening_balance' => $opening,
                'subtotal' => $subtotal,
                'payments_total' => $paymentsTotal,
                'closing_balance' => $closingBalance,
                'issued_at' => now(),
            ]);

            foreach ($payments as $payment) {
                $invoice->payments()->create([
                    'payment_id' => $payment->id,
                    'paid_at' => $payment->paid_at,
                    'method' => $payment->method,
                    'reference_number' => $payment->reference_number,
                    'amount' => $payment->amount,
                ]);
            }

            $entries = LedgerEntry::where('customer_id', $customer->id)
                ->whereIn('entry_type', ['delivery_charge', 'adjustment'])
                ->whereBetween('transaction_at', [$start, $end])
                ->orderBy('id')
                ->get();

            foreach ($entries as $entry) {
                $metadata = $entry->metadata ?? [];
                $isCharge = $entry->entry_type === 'delivery_charge';
                $amount = (float) $entry->amount;
                $invoice->lines()->create([
                    'delivery_line_id' => $isCharge ? $entry->delivery_line_id : null,
                    'product_name' => $isCharge
                        ? ($metadata['product_name'] ?? 'Delivered product')
                        : 'Adjustment · '.($metadata['product_name'] ?? 'Delivery'),
                    'unit' => $isCharge ? ($metadata['unit'] ?? 'unit') : 'adjustment',
                    'quantity' => $isCharge ? ($metadata['quantity'] ?? 0) : 1,
                    'unit_price' => $amount,
                    'line_total' => $entry->side === 'debit' ? $amount : -$amount,
                ]);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice generated successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('customer', 'lines', 'payments');
        $payments = $invoice->payments;

        return view('invoices.show', compact('invoice', 'payments'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('customer', 'lines', 'payments');
        $payments = $invoice->payments;

        return Pdf::loadView('invoices.print', compact('invoice', 'payments'))
            ->download($invoice->invoice_number.'.pdf');
    }
}
