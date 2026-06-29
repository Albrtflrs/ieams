<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .total { font-weight: bold; }
        .header { margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quotation #{{ $quotation->quotation_number }}</h1>
        <p><strong>Date Issued:</strong> {{ $quotation->date_issued->format('Y-m-d') }}</p>
        <p><strong>Valid Until:</strong> {{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : 'N/A' }}</p>
        <p><strong>Client:</strong> {{ $quotation->client_name }}</p>
        @if($quotation->client_address)
            <p><strong>Address:</strong> {{ $quotation->client_address }}</p>
        @endif
        <p><strong>Status:</strong> {{ $quotation->status }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Selling Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->selling_price, 2) }}</td>
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right"><strong>Subtotal</strong></td>
                <td class="right">{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            @if($quotation->discount > 0)
                <tr>
                    <td colspan="4" class="right">Discount ({{ $quotation->discount }}%)</td>
                    <td class="right">-{{ number_format($quotation->subtotal * $quotation->discount / 100, 2) }}</td>
                </tr>
            @endif
            @if($quotation->tax > 0)
                @php
                    $taxable = $quotation->subtotal - ($quotation->subtotal * $quotation->discount / 100);
                @endphp
                <tr>
                    <td colspan="4" class="right">Tax ({{ $quotation->tax }}%)</td>
                    <td class="right">{{ number_format($taxable * $quotation->tax / 100, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" class="right"><strong>Total Amount</strong></td>
                <td class="right"><strong>{{ number_format($quotation->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($quotation->notes)
        <p><strong>Notes:</strong> {{ $quotation->notes }}</p>
    @endif

    <div class="footer">
        Created on {{ $quotation->created_at->format('Y-m-d H:i') }}
    </div>
</body>
</html>