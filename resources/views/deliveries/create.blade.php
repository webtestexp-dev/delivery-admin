@extends('layouts.app')
@section('title', 'Add special order')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('deliveries.index') }}">← Deliveries</a><h1 class="page-heading h2 mt-2">Add special order</h1></div>
<form class="panel-card p-3 p-lg-4" method="POST" action="{{ route('deliveries.store') }}">
    @csrf<div class="row g-3 mb-4">
        <div class="col-12 col-md-6"><label class="form-label">Customer</label><select class="form-select" name="customer_id" required><option value="">Choose customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', request('customer_id')) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach</select></div>
        <div class="col-12 col-md-6"><label class="form-label">Delivery date</label><input class="form-control" type="date" name="delivery_date" value="{{ old('delivery_date', today()->toDateString()) }}" required></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea></div>
    </div>
    <h2 class="h5 fw-bold">Add products</h2>
    @forelse($products as $product)
    <div class="row align-items-center g-2 border-bottom py-2">
        <div class="col-1"><input class="form-check-input special-toggle" type="checkbox" name="lines[{{ $product->id }}][product_id]" value="{{ $product->id }}"></div>
        <div class="col-6 fw-semibold">{{ $product->name }} <span class="small text-secondary">· {{ $product->unit }}</span></div>
        <div class="col-5 col-md-3"><input class="form-control special-quantity" type="number" min="0.001" step="0.001" name="lines[{{ $product->id }}][ordered_quantity]" placeholder="Quantity" disabled></div>
    </div>
    @empty<p class="empty-state">No active products available.</p>@endforelse
    <div class="mt-4"><button class="btn btn-success">Create order</button><a class="btn btn-light" href="{{ route('deliveries.index') }}">Cancel</a></div>
</form>
<script>
document.querySelectorAll('.special-toggle').forEach((toggle) => toggle.addEventListener('change', () => {
    toggle.closest('.row').querySelector('.special-quantity').disabled = !toggle.checked;
}));
</script>
@endsection
