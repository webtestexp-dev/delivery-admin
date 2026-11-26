@extends('layouts.app')
@section('title', $customer->exists ? 'Edit customer' : 'Add customer')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('customers.index') }}">← Customers</a><h1 class="page-heading h2 mt-2">{{ $customer->exists ? 'Edit customer' : 'Add customer' }}</h1></div>
@php($prices = $customer->productPrices->keyBy('product_id'))
<form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
    @csrf @if ($customer->exists) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <section class="panel-card p-3 p-lg-4">
                <h2 class="h5 fw-bold mb-3">Customer details</h2>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $customer->name) }}" required maxlength="255"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $customer->phone) }}" required maxlength="30"></div>
                    <div class="col-12"><label class="form-label">Business name <span class="text-secondary">(optional)</span></label><input class="form-control" name="business_name" value="{{ old('business_name', $customer->business_name) }}"></div>
                    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3" required>{{ old('address', $customer->address) }}</textarea></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3">{{ old('notes', $customer->notes) }}</textarea></div>
                    @unless($customer->exists)
                        <div class="col-12"><label class="form-label">Opening balance due (₹)</label><input class="form-control" type="number" min="0" step="0.01" name="opening_balance" value="{{ old('opening_balance', '0.00') }}"><div class="form-text">Optional balance carried forward from before this system.</div></div>
                    @endunless
                    <div class="col-12 form-check ms-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer->exists ? $customer->is_active : true))><label class="form-check-label" for="is_active">Active customer</label></div>
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-5">
            <section class="panel-card p-3 p-lg-4">
                <h2 class="h5 fw-bold mb-1">Customer-specific prices</h2>
                <p class="small text-secondary mb-3">Leave blank to use the product’s current price.</p>
                @forelse ($products as $product)
                    <label class="form-label small fw-semibold mb-1" for="price-{{ $product->id }}">{{ $product->name }} <span class="text-secondary">· default ₹{{ number_format((float) $product->price, 2) }}{{ $product->is_active ? '' : ' · inactive' }}</span></label>
                    <input class="form-control mb-3" id="price-{{ $product->id }}" type="number" step="0.01" min="0" name="product_prices[{{ $product->id }}]" value="{{ old('product_prices.'.$product->id, $prices->get($product->id)?->price) }}" placeholder="Use default price">
                @empty
                    <p class="empty-state">Add active products to set customer-specific prices.</p>
                @endforelse
            </section>
        </div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-success">Save customer</button><a class="btn btn-light" href="{{ route('customers.index') }}">Cancel</a></div>
    </div>
</form>
@endsection
