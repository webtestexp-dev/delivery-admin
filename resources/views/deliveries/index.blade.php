@extends('layouts.app')
@section('title', 'Daily deliveries')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="muted-label text-uppercase mb-2">Route management</p><h1 class="page-heading h2 mb-0">Daily deliveries</h1></div><a class="btn btn-outline-success" href="{{ route('deliveries.create') }}">Add special order</a></div>
<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8"><form class="panel-card p-3 row g-2 align-items-end" method="GET">
        <div class="col-12 col-sm-4"><label class="form-label small">Delivery date</label><input class="form-control" type="date" name="date" value="{{ $date }}"></div>
        <div class="col-6 col-sm-3"><label class="form-label small">Status</label><select class="form-select" name="status"><option value="">All statuses</option>@foreach(['pending','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-6 col-sm-3"><label class="form-label small">Customer</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Name or phone"></div>
        <div class="col-12 col-sm-2"><button class="btn btn-outline-success w-100">Filter</button></div>
    </form></div>
    <div class="col-12 col-xl-4"><form class="panel-card p-3 h-100 d-flex flex-wrap align-items-end gap-2" method="POST" action="{{ route('deliveries.generate') }}">
        @csrf<input type="hidden" name="date" value="{{ $date }}"><div class="small text-secondary flex-grow-1">Create today’s list from active regular orders.</div><button class="btn btn-success">Generate daily list</button>
    </form></div>
</div>
<section class="panel-card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th class="admin-table-number">No.</th><th>Customer</th><th>Products</th><th>Delivery person</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($deliveries as $delivery)
<tr><td class="admin-table-number">{{ str_pad((string) ($deliveries->firstItem() + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td><div class="fw-semibold">{{ $delivery->customer->name }}</div><div class="small text-secondary">{{ $delivery->customer->phone }}</div></td><td>@foreach($delivery->lines as $line)<span class="d-block small">{{ $line->product_name }} · {{ $line->ordered_quantity }} {{ $line->unit }}</span>@endforeach</td><td>{{ $delivery->deliveryPerson?->name ?: 'Unassigned' }}</td><td><span class="status-pill status-{{ $delivery->status }}">{{ $delivery->status }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('deliveries.edit', $delivery) }}">Review / update</a></td></tr>
@empty<tr><td colspan="6" class="text-center empty-state py-5">No deliveries for {{ \Illuminate\Support\Carbon::parse($date)->format('M j, Y') }}.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $deliveries->links() }}</div></section>
@endsection
