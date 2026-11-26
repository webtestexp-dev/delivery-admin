@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="muted-label text-uppercase mb-2">Collections</p><h1 class="page-heading h2 mb-0">Payments</h1></div><a class="btn btn-success" href="{{ route('payments.create') }}">Record payment</a></div>
<form class="panel-card p-3 mb-3 row g-2 align-items-end" method="GET">
    <div class="col-6 col-md-3"><label class="form-label small">From</label><input class="form-control" type="date" name="from" value="{{ request('from') }}"></div>
    <div class="col-6 col-md-3"><label class="form-label small">To</label><input class="form-control" type="date" name="to" value="{{ request('to') }}"></div>
    <div class="col-9 col-md-4"><label class="form-label small">Customer</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Name or phone"></div>
    <div class="col-3 col-md-2"><button class="btn btn-outline-success w-100">Filter</button></div>
</form>
<section class="panel-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Receipt</th><th>Customer</th><th>Received</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th><th></th></tr></thead><tbody>
@forelse($payments as $payment)<tr><td class="admin-table-number">{{ str_pad((string) ($payments->firstItem() + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td>#{{ $payment->id }}</td><td class="fw-semibold">{{ $payment->customer->name }}</td><td>{{ $payment->paid_at->format('M j, Y · g:i A') }}</td><td class="text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</td><td>{{ $payment->reference_number ?: '—' }}</td><td class="text-end fw-semibold">₹{{ number_format((float) $payment->amount, 2) }}</td><td class="text-end"><a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-outline-secondary">Receipt</a></td></tr>
@empty<tr><td colspan="8" class="text-center empty-state py-5">No payments found.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $payments->links() }}</div></section>
@endsection
