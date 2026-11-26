@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><a class="small text-decoration-none" href="{{ route('invoices.index') }}">← Invoices</a><h1 class="page-heading h2 mt-2 mb-0">{{ $invoice->invoice_number }}</h1></div><div class="d-flex gap-2"><a class="btn btn-outline-success" href="{{ route('invoices.pdf', $invoice) }}">Download PDF</a><button class="btn btn-outline-secondary" onclick="window.print()">Print</button></div></div>
<section class="panel-card p-3 p-lg-5">
    <div class="d-flex justify-content-between border-bottom pb-3 mb-4"><div><h2 class="h4 fw-bold">{{ config('app.name') }}</h2><div>{{ $invoice->customer_name ?? $invoice->customer->name }}</div><div>{{ $invoice->customer_business_name ?? $invoice->customer->business_name }}</div><div class="text-secondary">{{ $invoice->customer_phone ?? $invoice->customer->phone }} · {{ $invoice->customer_address ?? $invoice->customer->address }}</div></div><div class="text-end"><div class="muted-label text-capitalize">{{ $invoice->period_type }} invoice</div><div>{{ $invoice->period_start->format('M j, Y') }} – {{ $invoice->period_end->format('M j, Y') }}</div><div class="small text-secondary">Issued {{ $invoice->issued_at->format('M j, Y') }}</div></div></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th class="admin-table-number">No.</th><th>Product</th><th>Quantity</th><th>Unit price</th><th class="text-end">Total</th></tr></thead><tbody>
    @forelse($invoice->lines as $line)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $line->product_name }}</td><td>{{ $line->quantity }} {{ $line->unit }}</td><td>₹{{ number_format((float) $line->unit_price, 2) }}</td><td class="text-end">₹{{ number_format((float) $line->line_total, 2) }}</td></tr>@empty<tr><td colspan="5" class="text-center empty-state">No delivered items in the period.</td></tr>@endforelse
    </tbody></table></div>
    <div class="row justify-content-end"><div class="col-12 col-md-6 col-lg-5">
        <div class="d-flex justify-content-between py-2"><span>Opening balance</span><span>₹{{ number_format((float) $invoice->opening_balance, 2) }}</span></div>
        <div class="d-flex justify-content-between py-2"><span>Charges / adjustments</span><span>₹{{ number_format((float) $invoice->subtotal, 2) }}</span></div>
        <div class="d-flex justify-content-between py-2"><span>Payments received</span><span>− ₹{{ number_format((float) $invoice->payments_total, 2) }}</span></div>
        <div class="d-flex justify-content-between border-top pt-3 mt-2 fw-bold fs-5"><span>Closing balance</span><span>₹{{ number_format((float) $invoice->closing_balance, 2) }}</span></div>
    </div></div>
    <h2 class="h6 fw-bold mt-4">Payments in this billing period</h2>
    <div class="table-responsive"><table class="table"><thead><tr><th class="admin-table-number">No.</th><th>Date</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th></tr></thead><tbody>
    @forelse($payments as $payment)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $payment->paid_at->format('M j, Y') }}</td><td class="text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</td><td>{{ $payment->reference_number ?: '—' }}</td><td class="text-end">₹{{ number_format((float) $payment->amount, 2) }}</td></tr>@empty<tr><td colspan="5" class="text-secondary">No payments in this period.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
