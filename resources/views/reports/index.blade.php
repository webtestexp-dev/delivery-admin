@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="muted-label text-uppercase mb-2">Analytics</p><h1 class="page-heading h2 mb-0">Reports</h1></div></div>
@if($report === 'customer-outstanding')<p class="text-secondary small">Balances shown as of {{ \Illuminate\Support\Carbon::parse($to)->format('M j, Y') }}.</p>@endif
<form class="panel-card p-3 p-lg-4 mb-3 row g-3 align-items-end" method="GET">
    <div class="col-12 col-md-4"><label class="form-label">Report</label><select class="form-select" name="report">
        <option value="daily-sales" @selected($report === 'daily-sales')>Daily sales</option>
        <option value="customer-outstanding" @selected($report === 'customer-outstanding')>Customer outstanding</option>
        <option value="payment-collection" @selected($report === 'payment-collection')>Payment collection</option>
        <option value="product-sales" @selected($report === 'product-sales')>Product-wise sales</option>
    </select></div>
    <div class="col-6 col-md-3"><label class="form-label">From</label><input class="form-control" type="date" name="from" value="{{ $from }}"></div>
    <div class="col-6 col-md-3"><label class="form-label">To</label><input class="form-control" type="date" name="to" value="{{ $to }}"></div>
    <div class="col-12 col-md-2"><button class="btn btn-outline-success w-100">Apply</button></div>
</form>
<div class="d-flex justify-content-end mb-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.index', ['report' => $report, 'from' => $from, 'to' => $to, 'export' => 'csv']) }}">Export CSV</a></div>
@php($headers = match($report) {'customer-outstanding' => ['Customer','Phone','Outstanding balance'], 'payment-collection' => ['Method','Payments','Amount'], 'product-sales' => ['Product','Unit','Quantity','Sales'], default => ['Date','Deliveries','Sales']})
<section class="panel-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="admin-table-number">No.</th>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead><tbody>
@forelse($rows as $row)<tr><td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headers) + 1 }}" class="text-center empty-state py-5">No report data found for this period.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
