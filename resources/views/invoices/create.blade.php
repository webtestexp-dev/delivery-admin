@extends('layouts.app')
@section('title', 'Generate invoice')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('invoices.index') }}">← Invoices</a><h1 class="page-heading h2 mt-2">Generate invoice</h1><p class="text-secondary">Invoices are immutable snapshots. A billing period can only be invoiced once per customer.</p></div>
<form class="panel-card p-3 p-lg-4" method="POST" action="{{ route('invoices.generate') }}">@csrf
    <div class="row g-3"><div class="col-12 col-md-4"><label class="form-label">Customer</label><select class="form-select" name="customer_id" required><option value="">Choose customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-4"><label class="form-label">Billing period</label><select class="form-select" name="period_type"><option value="daily">Daily</option><option value="weekly">Weekly (Monday–Sunday)</option><option value="monthly">Monthly</option></select></div>
    <div class="col-6 col-md-4"><label class="form-label">Date in period</label><input class="form-control" type="date" name="period_date" value="{{ today()->toDateString() }}" required></div>
    <div class="col-12"><button class="btn btn-success">Generate invoice</button><a class="btn btn-light" href="{{ route('invoices.index') }}">Cancel</a></div></div>
</form>
@endsection
