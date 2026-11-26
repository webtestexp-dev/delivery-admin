@extends('layouts.app')
@section('title', 'Record payment')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('payments.index') }}">← Payments</a><h1 class="page-heading h2 mt-2">Record payment</h1></div>
<form class="panel-card p-3 p-lg-4" method="POST" action="{{ route('payments.store') }}">
    @csrf<input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
    <div class="row g-3">
        <div class="col-12 col-md-6"><label class="form-label">Customer</label><select class="form-select" name="customer_id" required><option value="">Choose customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', request('customer_id')) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach</select></div>
        <div class="col-12 col-md-6"><label class="form-label">Amount (₹)</label><input class="form-control" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required></div>
        <div class="col-12 col-md-6"><label class="form-label">Payment method</label><select class="form-select" name="method" required><option value="cash">Cash</option><option value="google_pay">Google Pay</option><option value="paytm">Paytm</option></select></div>
        <div class="col-12 col-md-6"><label class="form-label">Date and time</label><input class="form-control" type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required></div>
        <div class="col-12 col-md-6"><label class="form-label">Reference number</label><input class="form-control" name="reference_number" value="{{ old('reference_number') }}"></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea></div>
        <div class="col-12"><button class="btn btn-success">Save payment</button><a class="btn btn-light" href="{{ route('payments.index') }}">Cancel</a></div>
    </div>
</form>
@endsection
