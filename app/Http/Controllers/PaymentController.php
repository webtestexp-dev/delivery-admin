<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Services\PaymentRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);
        $payments = Payment::with('customer')
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('paid_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('paid_at', '<=', $date))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->whereHas('customer', fn ($customer) => $customer
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%')))
            ->orderByDesc('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create(): View
    {
        return view('payments.create', [
            'customers' => Customer::orderBy('name')->get(),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, PaymentRecorder $recorder): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'method' => ['required', 'in:cash,google_pay,paytm'],
            'paid_at' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'uuid'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $payment = $recorder->record($data, $request->user()->id);

        return redirect()->route('payments.receipt', $payment)->with('status', 'Payment recorded successfully.');
    }

    public function receipt(Payment $payment): View
    {
        return view('payments.receipt', ['payment' => $payment->load('customer', 'recordedBy')]);
    }
}
