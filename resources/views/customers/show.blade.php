@extends('layouts.app')
@section('title', $customer->name)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><a class="small text-decoration-none" href="{{ route('customers.index') }}">← Customers</a><h1 class="page-heading h2 mt-2 mb-1">{{ $customer->name }}</h1><p class="text-secondary mb-0">{{ $customer->phone }} · {{ $customer->address }}</p></div>
    <a class="btn btn-outline-success" href="{{ route('customers.edit', $customer) }}">Edit customer</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><section class="stat-card p-4"><div class="stat-label mb-2">Outstanding balance</div><div class="stat-value">₹{{ number_format((float) $balance, 2) }}</div></section></div>
    <div class="col-12 col-md-4"><section class="stat-card p-4"><div class="stat-label mb-2">Business</div><div class="h5 mb-0">{{ $customer->business_name ?: 'Personal customer' }}</div></section></div>
    <div class="col-12 col-md-4"><section class="stat-card p-4"><div class="stat-label mb-2">Status</div><span class="status-pill {{ $customer->is_active ? 'status-delivered' : 'status-cancelled' }}">{{ $customer->is_active ? 'Active' : 'Inactive' }}</span></section></div>
</div>
<div class="row g-3">
    <div class="col-12 col-xl-7"><section class="panel-card overflow-hidden">
        <div class="p-3 p-lg-4 pb-2"><h2 class="h5 fw-bold mb-0">Delivery history</h2></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Date</th><th>Items</th><th>Status</th></tr></thead><tbody>
        @forelse ($customer->deliveries as $delivery)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $delivery->delivery_date->format('M j, Y') }}</td><td>{{ $delivery->lines_count }}</td><td><span class="status-pill status-{{ $delivery->status }}">{{ $delivery->status }}</span></td></tr>
        @empty<tr><td colspan="4" class="text-center empty-state py-4">No delivery history yet.</td></tr>@endforelse
        </tbody></table></div>
    </section></div>
    <div class="col-12 col-xl-5"><section class="panel-card overflow-hidden">
        <div class="p-3 p-lg-4 pb-2 d-flex justify-content-between"><h2 class="h5 fw-bold mb-0">Recent payments</h2><a href="{{ route('payments.create', ['customer_id' => $customer->id]) }}" class="small">Record payment</a></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Date</th><th>Method</th><th class="text-end">Amount</th></tr></thead><tbody>
        @forelse ($customer->payments as $payment)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $payment->paid_at->format('M j, Y') }}</td><td class="text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</td><td class="text-end">₹{{ number_format((float) $payment->amount, 2) }}</td></tr>
        @empty<tr><td colspan="4" class="text-center empty-state py-4">No payments yet.</td></tr>@endforelse
        </tbody></table></div>
    </section></div>
    <div class="col-12"><section class="panel-card overflow-hidden">
        <div class="p-3 p-lg-4 pb-2"><h2 class="h5 fw-bold mb-0">Financial ledger</h2></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Date</th><th>Entry</th><th>Description</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead><tbody>
        @forelse ($customer->ledgerEntries as $entry)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $entry->transaction_at->format('M j, Y') }}</td><td class="text-capitalize">{{ str_replace('_', ' ', $entry->entry_type) }}</td><td>{{ $entry->description ?: '—' }}</td><td class="text-end">{{ $entry->side === 'debit' ? '₹'.number_format((float) $entry->amount, 2) : '—' }}</td><td class="text-end">{{ $entry->side === 'credit' ? '₹'.number_format((float) $entry->amount, 2) : '—' }}</td></tr>
        @empty<tr><td colspan="6" class="text-center empty-state py-4">No ledger entries yet.</td></tr>@endforelse
        </tbody></table></div>
    </section></div>
    <div class="col-12"><section class="panel-card overflow-hidden">
        <div class="p-3 p-lg-4 pb-2 d-flex justify-content-between"><h2 class="h5 fw-bold mb-0">Invoices</h2><a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="small">All invoices</a></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Invoice</th><th>Period</th><th>Issued</th><th class="text-end">Closing balance</th></tr></thead><tbody>
        @forelse ($customer->invoices as $invoice)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td><td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td><td class="text-capitalize">{{ $invoice->period_type }} · {{ $invoice->period_start->format('M j') }} – {{ $invoice->period_end->format('M j, Y') }}</td><td>{{ $invoice->issued_at->format('M j, Y') }}</td><td class="text-end">₹{{ number_format((float) $invoice->closing_balance, 2) }}</td></tr>
        @empty<tr><td colspan="5" class="text-center empty-state py-4">No invoices generated yet.</td></tr>@endforelse
        </tbody></table></div>
    </section></div>
</div>
@endsection
