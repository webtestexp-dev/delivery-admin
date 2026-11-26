@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><p class="muted-label text-uppercase mb-2">Customer directory</p><h1 class="page-heading h2 mb-0">Customers</h1></div>
    <a class="btn btn-success" href="{{ route('customers.create') }}">Add customer</a>
</div>
<section class="panel-card p-3 p-lg-4 mb-3">
    <form method="GET" class="row g-2">
        <div class="col"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, phone or business"></div>
        <div class="col-auto"><button class="btn btn-outline-success">Search</button></div>
        @if (request('q'))<div class="col-auto"><a class="btn btn-light" href="{{ route('customers.index') }}">Clear</a></div>@endif
    </form>
</section>
<section class="panel-card overflow-hidden">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th class="admin-table-number">No.</th><th>Customer</th><th>Phone</th><th>Business</th><th>Deliveries</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($customers as $customer)
            <tr>
                <td class="admin-table-number">{{ str_pad((string) ($customers->firstItem() + $loop->index), 2, '0', STR_PAD_LEFT) }}</td>
                <td><a class="fw-semibold text-decoration-none text-dark" href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></td>
                <td>{{ $customer->phone }}</td><td>{{ $customer->business_name ?: '—' }}</td><td>{{ $customer->deliveries_count }}</td>
                <td><span class="status-pill {{ $customer->is_active ? 'status-delivered' : 'status-cancelled' }}">{{ $customer->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('customers.edit', $customer) }}">Edit</a>
                    <form class="d-inline" method="POST" action="{{ route('customers.status', $customer) }}" onsubmit="return confirm('Change this customer’s active status?')">
                        @csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $customer->is_active ? 0 : 1 }}">
                        <button class="btn btn-sm btn-outline-secondary">{{ $customer->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center empty-state py-5">No customers found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="p-3">{{ $customers->links() }}</div>
</section>
@endsection
