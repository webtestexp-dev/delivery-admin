@extends('layouts.app')
@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('products.index') }}">← Products</a><h1 class="page-heading h2 mt-2">{{ $product->exists ? 'Edit product' : 'Add product' }}</h1></div>
<form class="panel-card p-3 p-lg-4" method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
    @csrf @if($product->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-12 col-md-6"><label class="form-label">Product name</label><input class="form-control" name="name" value="{{ old('name', $product->name) }}" required></div>
        <div class="col-12 col-md-6"><label class="form-label">Brand</label><select class="form-select" name="brand_id"><option value="">No brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
        <div class="col-12 col-md-4"><label class="form-label">Unit</label><input class="form-control" name="unit" value="{{ old('unit', $product->unit) }}" placeholder="packet, kg, loaf" required></div>
        <div class="col-6 col-md-4"><label class="form-label">Packet size</label><input class="form-control" type="number" min="0" step="0.001" name="packet_size" value="{{ old('packet_size', $product->packet_size) }}"></div>
        <div class="col-6 col-md-4"><label class="form-label">Size unit</label><input class="form-control" name="packet_size_unit" value="{{ old('packet_size_unit', $product->packet_size_unit) }}" placeholder="ml, g, kg"></div>
        <div class="col-12 col-md-4"><label class="form-label">Current price (₹)</label><input class="form-control" type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01" required></div>
        <div class="col-12"><button class="btn btn-success">Save product</button><a class="btn btn-light" href="{{ route('products.index') }}">Cancel</a></div>
    </div>
</form>
@endsection
