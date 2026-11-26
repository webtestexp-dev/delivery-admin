@extends('layouts.app')
@section('title', 'Regular orders')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="muted-label text-uppercase mb-2">Recurring schedule</p><h1 class="page-heading h2 mb-0">Regular orders</h1></div><a class="btn btn-success" href="{{ route('recurring-orders.create') }}">Create regular order</a></div>
<section class="panel-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th><th>Customer</th><th>Products</th><th>Schedule</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td class="admin-table-number">{{ str_pad((string) ($orders->firstItem() + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td class="fw-semibold">{{ $order->customer->name }}</td><td>@foreach($order->lines as $line)<span class="d-block">{{ $line->product->name }} · {{ $line->quantity }} {{ $line->product->unit }}</span>@endforeach</td><td>{{ $order->starts_on->format('M j, Y') }}@if($order->ends_on) – {{ $order->ends_on->format('M j, Y') }}@else onward @endif</td><td><span class="status-pill {{ $order->is_active ? 'status-delivered' : 'status-cancelled' }}">{{ $order->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('recurring-orders.edit', $order) }}">Edit</a></td></tr>
@empty<tr><td colspan="6" class="text-center empty-state py-5">No recurring orders yet.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $orders->links() }}</div></section>
@endsection
