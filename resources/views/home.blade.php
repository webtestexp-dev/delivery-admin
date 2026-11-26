@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <p class="muted-label text-uppercase mb-2">Operations overview</p>
        <h1 class="page-heading h2 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">{{ $today->format('l, F j, Y') }}</p>
    </div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $completedDeliveriesToday }} of {{ $deliveriesToday }} deliveries completed</span>
</div>

<div class="row g-3 g-xl-4 mb-4">
    <div class="col-6 col-xl-3">
        <section class="stat-card p-3 p-lg-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3"><span class="stat-label">Total customers</span><span class="stat-icon">C</span></div>
            <div class="stat-value">{{ number_format($totalCustomers) }}</div>
            <div class="small text-secondary mt-1">{{ number_format($activeCustomers) }} active</div>
        </section>
    </div>
    <div class="col-6 col-xl-3">
        <section class="stat-card p-3 p-lg-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3"><span class="stat-label">Products &amp; brands</span><span class="stat-icon">P</span></div>
            <div class="stat-value">{{ number_format($totalProducts) }}</div>
            <div class="small text-secondary mt-1">{{ number_format($totalBrands) }} total brands · {{ number_format($activeBrands) }} active</div>
        </section>
    </div>
    <div class="col-6 col-xl-3">
        <section class="stat-card p-3 p-lg-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3"><span class="stat-label">Today's sales</span><span class="stat-icon">₹</span></div>
            <div class="stat-value">₹{{ number_format((float) $salesToday, 2) }}</div>
            <div class="small text-secondary mt-1">Delivered orders only</div>
        </section>
    </div>
    <div class="col-6 col-xl-3">
        <section class="stat-card p-3 p-lg-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3"><span class="stat-label">Payments received</span><span class="stat-icon">₹</span></div>
            <div class="stat-value">₹{{ number_format((float) $paymentsToday, 2) }}</div>
            <div class="small text-secondary mt-1">Received today</div>
        </section>
    </div>
    <div class="col-12">
        <section class="stat-card d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 p-lg-4">
            <div>
                <div class="stat-label mb-2">Total outstanding balances</div>
                <div class="stat-value">₹{{ number_format((float) $outstandingBalance, 2) }}</div>
            </div>
            <div class="text-secondary small">Calculated from customer ledger entries</div>
        </section>
    </div>
</div>

<div class="row g-3 g-xl-4">
    <div class="col-12 col-xl-7">
        <section class="panel-card h-100 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center p-3 p-lg-4 pb-2">
                <div><h2 class="h5 fw-bold mb-1">Recent deliveries</h2><p class="small text-secondary mb-0">Latest delivery records</p></div>
                <span class="badge text-bg-light border">{{ number_format($deliveriesToday) }} today</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="admin-table-number">No.</th><th>Customer</th><th>Date</th><th>Items</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($recentDeliveries as $delivery)
                            <tr>
                                <td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="fw-semibold">{{ $delivery->customer->name }}</td>
                                <td class="text-secondary text-nowrap">{{ $delivery->delivery_date->format('M j, Y') }}</td>
                                <td>{{ $delivery->lines_count }}</td>
                                <td><span class="status-pill status-{{ $delivery->status }}">{{ $delivery->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center empty-state py-5">No deliveries recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-5">
        <section class="panel-card h-100 overflow-hidden">
            <div class="p-3 p-lg-4 pb-2">
                <h2 class="h5 fw-bold mb-1">Recent payments</h2>
                <p class="small text-secondary mb-0">Latest customer collections</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="admin-table-number">No.</th><th>Customer</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $payment->customer->name }}</div>
                                    <div class="small text-secondary">{{ $payment->paid_at->format('M j, Y · g:i A') }}</div>
                                </td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                                <td class="text-end fw-semibold text-nowrap">₹{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center empty-state py-5">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
