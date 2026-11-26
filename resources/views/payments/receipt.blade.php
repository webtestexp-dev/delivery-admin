@extends('layouts.app')
@section('title', 'Payment receipt')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><a class="small text-decoration-none" href="{{ route('payments.index') }}">← Payments</a><h1 class="page-heading h2 mt-2 mb-0">Payment receipt</h1></div><button class="btn btn-outline-secondary" onclick="window.print()">Print</button></div>
<section class="panel-card p-4 p-lg-5">
    <div class="d-flex justify-content-between border-bottom pb-4 mb-4"><div><h2 class="h4 fw-bold">{{ config('app.name') }}</h2><p class="text-secondary mb-0">Payment received</p></div><div class="text-end"><div class="muted-label">Receipt no.</div><div class="fw-bold">PAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div></div></div>
    <div class="row g-4 mb-4"><div class="col-6"><div class="muted-label">Received from</div><div class="fw-semibold">{{ $payment->customer->name }}</div><div class="text-secondary">{{ $payment->customer->phone }}</div></div><div class="col-6 text-end"><div class="muted-label">Date</div><div>{{ $payment->paid_at->format('F j, Y · g:i A') }}</div></div></div>
    <div class="row border-top border-bottom py-3"><div class="col">Payment · {{ str_replace('_', ' ', $payment->method) }}</div><div class="col text-end h5 fw-bold mb-0">₹{{ number_format((float) $payment->amount, 2) }}</div></div>
    @if($payment->reference_number)<p class="mt-3 mb-0"><span class="muted-label">Reference:</span> {{ $payment->reference_number }}</p>@endif
    @if($payment->notes)<p class="mt-2 mb-0"><span class="muted-label">Notes:</span> {{ $payment->notes }}</p>@endif
    <p class="small text-secondary mt-4 mb-0">Thank you. This receipt confirms a recorded payment.</p>
</section>
@endsection
