@extends('layouts.app')
@section('title', 'Brands')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('products.index') }}">← Products</a><h1 class="page-heading h2 mt-2">Brands</h1></div>
<form class="panel-card p-3 p-lg-4 mb-4" method="POST" action="{{ route('brands.store') }}">
    @csrf<h2 class="h5 fw-bold">Add brand</h2>
    <div class="row g-2"><div class="col-md-4"><input class="form-control" name="name" required placeholder="Brand name"></div><div class="col-md-6"><input class="form-control" name="description" placeholder="Description (optional)"></div><div class="col-md-2"><button class="btn btn-success w-100">Add brand</button></div></div>
</form>
<div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">All brands</h2><a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">Products</a></div>
<div class="row g-3">
@forelse ($brands as $brand)
    <div class="col-12 col-lg-6"><section class="panel-card p-3">
        <form method="POST" action="{{ route('brands.update', $brand) }}">
            @csrf @method('PUT')
            <div class="row g-2 align-items-center"><div class="col-md-5"><input class="form-control" name="name" value="{{ $brand->name }}" required></div><div class="col-md-5"><input class="form-control" name="description" value="{{ $brand->description }}" placeholder="Description"></div><div class="col-md-2"><button class="btn btn-outline-success w-100">Save</button></div></div>
        </form>
        <div class="d-flex justify-content-between align-items-center mt-2"><span class="small text-secondary">{{ $brand->products_count }} product(s) · {{ $brand->is_active ? 'Active' : 'Inactive' }}</span>
            <form method="POST" action="{{ route('brands.status', $brand) }}" onsubmit="return confirm('Change this brand’s active status?')">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $brand->is_active ? 0 : 1 }}"><button class="btn btn-sm btn-outline-secondary">{{ $brand->is_active ? 'Deactivate' : 'Activate' }}</button></form>
        </div>
    </section></div>
@empty<p class="empty-state">No brands created yet.</p>@endforelse
</div>
<div class="mt-3">{{ $brands->links() }}</div>
@endsection
