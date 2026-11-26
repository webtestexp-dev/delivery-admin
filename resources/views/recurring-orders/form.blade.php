@extends('layouts.app')
@section('title', $order->exists ? 'Edit regular order' : 'Create regular order')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('recurring-orders.index') }}">← Regular orders</a><h1 class="page-heading h2 mt-2">{{ $order->exists ? 'Edit regular order' : 'Create regular order' }}</h1><p class="text-secondary">Choose default products and quantities. Daily delivery quantities and prices can be adjusted independently.</p></div>
@php($selected = $order->lines->keyBy('product_id'))
<form class="panel-card p-3 p-lg-4" method="POST" action="{{ $order->exists ? route('recurring-orders.update', $order) : route('recurring-orders.store') }}">
    @csrf @if($order->exists) @method('PUT') @endif
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6"><label class="form-label">Customer</label><select name="customer_id" class="form-select" required><option value="">Choose customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', $order->customer_id) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach</select></div>
        <div class="col-6 col-md-3"><label class="form-label">Starts on</label><input class="form-control" type="date" name="starts_on" value="{{ old('starts_on', $order->exists ? $order->starts_on->toDateString() : today()->toDateString()) }}" required></div>
        <div class="col-6 col-md-3"><label class="form-label">Ends on</label><input class="form-control" type="date" name="ends_on" value="{{ old('ends_on', $order->ends_on?->toDateString()) }}"></div>
        <div class="col-12 form-check ms-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $order->exists ? $order->is_active : true))><label class="form-check-label" for="is_active">Active recurring order</label></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes', $order->notes) }}</textarea></div>
    </div>
    <h2 class="h5 fw-bold mb-3">Default daily products</h2>
    @forelse($products as $product)
        @php($line = $selected->get($product->id))
        <div class="row g-2 align-items-center border-bottom py-2">
            <div class="col-1"><input class="form-check-input product-toggle" type="checkbox" name="lines[{{ $product->id }}][product_id]" value="{{ $product->id }}" @checked($line)></div>
            <div class="col-5 col-md-4"><span class="fw-semibold">{{ $product->name }}</span><span class="small text-secondary"> · {{ $product->unit }} · ₹{{ number_format((float) $product->price, 2) }}</span></div>
            <div class="col-3"><input class="form-control line-quantity" type="number" min="0.001" step="0.001" name="lines[{{ $product->id }}][quantity]" value="{{ old('lines.'.$product->id.'.quantity', $line?->quantity) }}" placeholder="Qty" @disabled(!$line)></div>
            <div class="col-3"><input class="form-control line-price" type="number" min="0" step="0.01" name="lines[{{ $product->id }}][unit_price]" value="{{ old('lines.'.$product->id.'.unit_price', $line?->unit_price) }}" placeholder="Default price" @disabled(!$line)></div>
        </div>
    @empty<p class="empty-state">Add active products before creating a recurring order.</p>@endforelse
    <div class="mt-4"><button class="btn btn-success">Save recurring order</button><a class="btn btn-light" href="{{ route('recurring-orders.index') }}">Cancel</a></div>
</form>
<script>
document.querySelectorAll('.product-toggle').forEach((toggle) => {
    toggle.addEventListener('change', () => {
        const row = toggle.closest('.row');
        row.querySelectorAll('.line-quantity, .line-price').forEach((input) => { input.disabled = !toggle.checked; });
        if (toggle.checked && !row.querySelector('.line-quantity').value) row.querySelector('.line-quantity').value = '1';
    });
});
</script>
@endsection
