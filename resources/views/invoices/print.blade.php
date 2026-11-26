<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 28px 36px;
        }

        body {
            color: #17211b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }

        h2 {
            font-size: 15px;
            margin: 24px 0 8px;
        }

        .muted {
            color: #66736b;
        }

        .header,
        .customer,
        .summary-layout,
        .items,
        .payments,
        .summary {
            border-collapse: collapse;
            width: 100%;
        }

        .header td,
        .customer td {
            padding: 8px 0;
            vertical-align: top;
        }

        .invoice-meta {
            text-align: right;
        }

        .customer {
            border-top: 1px solid #dfe5df;
            border-bottom: 1px solid #dfe5df;
            margin: 12px 0;
        }

        .items,
        .payments {
            margin: 16px 0;
        }

        .items th,
        .items td,
        .payments th,
        .payments td {
            border-bottom: 1px solid #dfe5df;
            padding: 8px;
            text-align: left;
        }

        .row-number {
            color: #66736b;
            text-align: center !important;
            width: 36px;
        }

        .items th,
        .payments th {
            background: #f4f7f4;
            color: #53625a;
        }

        .amount {
            text-align: right !important;
            white-space: nowrap;
        }

        .items thead,
        .payments thead {
            display: table-header-group;
        }

        .summary-layout td {
            vertical-align: top;
        }

        .summary {
            margin-left: auto;
        }

        .summary td {
            border: 0;
            padding: 5px 0;
        }

        .total td {
            border-top: 1px solid #17211b;
            font-size: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>{{ config('app.name') }}</h1>
                <div class="muted">Invoice {{ $invoice->invoice_number }}</div>
            </td>
            <td class="invoice-meta">
                <strong class="text-capitalize">{{ $invoice->period_type }} invoice</strong><br>
                {{ $invoice->period_start->format('M j, Y') }} – {{ $invoice->period_end->format('M j, Y') }}<br>
                Issued {{ $invoice->issued_at->format('M j, Y') }}
            </td>
        </tr>
    </table>

    <table class="customer">
        <tr>
            <td>
                <strong>{{ $invoice->customer_name ?? $invoice->customer->name }}</strong><br>
                {{ $invoice->customer_business_name ?? $invoice->customer->business_name }}<br>
                {{ $invoice->customer_address ?? $invoice->customer->address }}<br>
                {{ $invoice->customer_phone ?? $invoice->customer->phone }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="row-number">No.</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit price</th>
                <th class="amount">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->lines as $line)
                <tr>
                    <td class="row-number">{{ $loop->iteration }}</td>
                    <td>{{ $line->product_name }}</td>
                    <td>{{ $line->quantity }} {{ $line->unit }}</td>
                    <td>₹{{ number_format((float) $line->unit_price, 2) }}</td>
                    <td class="amount">₹{{ number_format((float) $line->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No delivered items in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-layout">
        <tr>
            <td style="width: 45%"></td>
            <td style="width: 55%">
                <table class="summary">
                    <tr>
                        <td>Opening balance</td>
                        <td class="amount">₹{{ number_format((float) $invoice->opening_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Charges / adjustments</td>
                        <td class="amount">₹{{ number_format((float) $invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Payments received</td>
                        <td class="amount">− ₹{{ number_format((float) $invoice->payments_total, 2) }}</td>
                    </tr>
                    <tr class="total">
                        <td>Closing balance</td>
                        <td class="amount">₹{{ number_format((float) $invoice->closing_balance, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <h2>Payments received</h2>
    <table class="payments">
        <thead>
            <tr>
                <th class="row-number">No.</th>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td class="row-number">{{ $loop->iteration }}</td>
                    <td>{{ $payment->paid_at->format('M j, Y') }}</td>
                    <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                    <td>{{ $payment->reference_number ?: '—' }}</td>
                    <td class="amount">₹{{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No payments in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
