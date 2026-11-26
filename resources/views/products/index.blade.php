@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><p class="muted-label text-uppercase mb-2">Catalog</p><h1 class="page-heading h2 mb-0">Products</h1></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('brands.index') }}">Manage brands</a><a class="btn btn-success" href="{{ route('products.create') }}">Add product</a></div>
</div>
<section class="panel-card p-3 mb-3"><form class="row g-2" method="GET"><div class="col"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search products"></div><div class="col-auto"><button class="btn btn-outline-success">Search</button></div></form></section>
<section class="panel-card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th class="admin-table-number">No.</th><th>Product</th><th>Brand</th><th>Unit / size</th><th>Price</th><th>Status</th><th></th></tr></thead><tbody>
@forelse ($products as $product)
<tr><td class="admin-table-number">{{ str_pad((string) ($products->firstItem() + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td class="fw-semibold">{{ $product->name }}</td><td>{{ $product->brand?->name ?: '—' }}</td><td>{{ $product->unit }}@if($product->packet_size) · {{ $product->packet_size }} {{ $product->packet_size_unit }}@endif</td><td>₹{{ number_format((float) $product->price, 2) }}</td>
<td><span class="status-pill {{ $product->is_active ? 'status-delivered' : 'status-cancelled' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
<td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="{{ route('products.edit', $product) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('products.status', $product) }}" onsubmit="return confirm('Change this product’s active status?')">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $product->is_active ? 0 : 1 }}"><button class="btn btn-sm btn-outline-secondary">{{ $product->is_active ? 'Deactivate' : 'Activate' }}</button></form></td></tr>
@empty<tr><td colspan="7" class="text-center empty-state py-5">No products found.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $products->links() }}</div></section>
@endsection
