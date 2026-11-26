@extends('layouts.app')
@section('title', 'Review delivery')
@section('content')
<div class="mb-4"><a class="small text-decoration-none" href="{{ route('deliveries.index', ['date' => $delivery->delivery_date->toDateString()]) }}">← Daily deliveries</a><h1 class="page-heading h2 mt-2">Review delivery</h1><p class="text-secondary mb-0">{{ $delivery->customer->name }} · {{ $delivery->delivery_date->format('M j, Y') }}</p></div>
<form class="panel-card p-3 p-lg-4" method="POST" action="{{ route('deliveries.update', $delivery) }}">
    @csrf @method('PUT')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4"><label class="form-label">Status</label><select class="form-select" name="status" required>@foreach(['pending','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status', $delivery->status) === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-6 col-md-4"><label class="form-label">Delivery person</label><select class="form-select" name="delivery_person_id"><option value="">Unassigned</option>@foreach($deliveryPeople as $person)<option value="{{ $person->id }}" @selected(old('delivery_person_id', $delivery->delivery_person_id) == $person->id)>{{ $person->name }}</option>@endforeach</select></div>
        <div class="col-12 col-md-4"><label class="form-label">Notes</label><input class="form-control" name="notes" value="{{ old('notes', $delivery->notes) }}"></div>
    </div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th class="admin-table-number">No.</th><th>Product</th><th>Ordered</th><th>Delivered</th><th>Unit price (₹)</th><th>Line total (₹)</th></tr></thead><tbody>
    @foreach($delivery->lines as $line)
    <tr>
        <td class="admin-table-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
        <td class="fw-semibold">{{ $line->product_name }} <span class="small text-secondary">{{ $line->unit }}</span><input type="hidden" name="lines[{{ $loop->index }}][id]" value="{{ $line->id }}"></td>
        <td><input class="form-control" type="number" min="0.001" step="0.001" name="lines[{{ $loop->index }}][ordered_quantity]" value="{{ old('lines.'.$loop->index.'.ordered_quantity', $line->ordered_quantity) }}" required></td>
        <td><input class="form-control delivered-quantity" type="number" min="0" step="0.001" name="lines[{{ $loop->index }}][delivered_quantity]" value="{{ old('lines.'.$loop->index.'.delivered_quantity', $line->delivered_quantity) }}" required></td>
        <td><input class="form-control unit-price" type="number" min="0" step="0.01" name="lines[{{ $loop->index }}][unit_price]" value="{{ old('lines.'.$loop->index.'.unit_price', $line->unit_price) }}" required></td>
        <td class="line-total text-nowrap">₹{{ number_format((float) $line->line_total, 2) }}</td>
    </tr>
    @endforeach
    </tbody></table></div>
    <p class="small text-secondary">Saving delivered quantities updates the balance through new ledger entries; prior financial entries are never overwritten.</p>
    <button class="btn btn-success">Save delivery</button><a class="btn btn-light" href="{{ route('deliveries.index', ['date' => $delivery->delivery_date->toDateString()]) }}">Cancel</a>
</form>
<script>
document.querySelectorAll('.delivered-quantity, .unit-price').forEach((input) => input.addEventListener('input', () => {
    const row = input.closest('tr');
    const total = (Number(row.querySelector('.delivered-quantity').value || 0) * Number(row.querySelector('.unit-price').value || 0)).toFixed(2);
    row.querySelector('.line-total').textContent = `₹${total}`;
}));
</script>
@endsection
