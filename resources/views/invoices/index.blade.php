@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="muted-label text-uppercase mb-2">Billing</p><h1 class="page-heading h2 mb-0">Invoices</h1></div><a class="btn btn-success" href="{{ route('invoices.create') }}">Generate invoice</a></div>
<form class="panel-card p-3 mb-3 row g-2 align-items-end" method="GET">
    <div class="col-12 col-md-4"><label class="form-label small">Customer</label><select class="form-select" name="customer_id"><option value="">All customers</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><label class="form-label small">From</label><input class="form-control" type="date" name="from" value="{{ request('from') }}"></div>
    <div class="col-6 col-md-3"><label class="form-label small">To</label><input class="form-control" type="date" name="to" value="{{ request('to') }}"></div>
    <div class="col-12 col-md-2"><button class="btn btn-outline-success w-100">Filter</button></div>
</form>
<section class="panel-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Invoice</th><th>Customer</th><th>Period</th><th>Issued</th><th>Closing balance</th><th></th></tr></thead><tbody>
@forelse($invoices as $invoice)<tr><td class="admin-table-number">{{ str_pad((string) ($invoices->firstItem() + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td class="fw-semibold">{{ $invoice->invoice_number }}</td><td>{{ $invoice->customer_name ?? $invoice->customer->name }}</td><td class="text-capitalize">{{ $invoice->period_type }} · {{ $invoice->period_start->format('M j') }} – {{ $invoice->period_end->format('M j, Y') }}</td><td>{{ $invoice->issued_at->format('M j, Y') }}</td><td>₹{{ number_format((float) $invoice->closing_balance, 2) }}</td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="{{ route('invoices.show', $invoice) }}">View</a> <a class="btn btn-sm btn-outline-success" href="{{ route('invoices.pdf', $invoice) }}">PDF</a></td></tr>
@empty<tr><td colspan="7" class="text-center empty-state py-5">No invoices found.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $invoices->links() }}</div></section>
@endsection
