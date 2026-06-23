<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .summary { font-size: 14px; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Financial Report</h1>
    <p>Period: {{ $data['period'] }} ({{ $data['month'] ?? 'N/A' }})</p>

    <h2>Summary</h2>
    <table>
        <tr><th>Total Income</th><td class="text-right">{{ number_format($data['summary']['total_income'], 2) }}</td></tr>
        <tr><th>Total Expenses</th><td class="text-right">{{ number_format($data['summary']['total_expenses'], 2) }}</td></tr>
        <tr><th>Net Profit</th><td class="text-right">{{ number_format($data['summary']['net_profit'], 2) }}</td></tr>
    </table>

    <h2>Income by Category</h2>
    <table>
        <tr><th>Category</th><th>Amount</th></tr>
        @foreach($data['income_by_category'] as $row)
        <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ number_format($row['value'], 2) }}</td></tr>
        @endforeach
    </table>

    <h2>Expense by Category</h2>
    <table>
        <tr><th>Category</th><th>Amount</th></tr>
        @foreach($data['expense_by_category'] as $row)
        <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ number_format($row['value'], 2) }}</td></tr>
        @endforeach
    </table>

    <h2>Monthly Trend</h2>
    <table>
        <tr><th>Month</th><th>Income</th><th>Expenses</th></tr>
        @foreach($data['months'] as $i => $month)
        <tr>
            <td>{{ $month }}</td>
            <td class="text-right">{{ number_format($data['income_trend'][$i], 2) }}</td>
            <td class="text-right">{{ number_format($data['expense_trend'][$i], 2) }}</td>
        </tr>
        @endforeach
    </table>

    <h2>Top Clients</h2>
    <table>
        <tr><th>Client</th><th>Revenue</th></tr>
        @foreach($data['top_clients'] as $c)
        <tr><td>{{ $c['name'] }}</td><td class="text-right">{{ number_format($c['total'], 2) }}</td></tr>
        @endforeach
    </table>

    <h2>Top Suppliers</h2>
    <table>
        <tr><th>Supplier</th><th>Expenses</th></tr>
        @foreach($data['top_suppliers'] as $s)
        <tr><td>{{ $s['name'] }}</td><td class="text-right">{{ number_format($s['total'], 2) }}</td></tr>
        @endforeach
    </table>

    <p style="margin-top: 30px; font-size: 10px; color: #888;">Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
</body>
</html>