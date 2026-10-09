<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receivables Aging Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: right; }
        th { background: #f2f2f2; text-align: center; }
        .text-left { text-align: left; }
        .total-row { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body>
    {{-- Watermark logo --}}
    @php
        $logoFullPath = public_path('images/logob.jpg');
        if (!empty($logoPath) && Storage::disk('public')->exists($logoPath)) {
            $logoFullPath = public_path('storage/' . $logoPath);
        }
    @endphp
    @if($logoFullPath)
        <img src="{{ $logoFullPath }}" 
            style="position: fixed; 
            top: 50%; 
            left: 0; 
            right: 0; 
            transform: translateY(-50%); 
            margin: 0 auto; 
            opacity: 0.06; 
            width: 100%; 
            max-width: 900px;
            height: auto; 
            max-height: 90%; 
            z-index: 0;
            pointer-events: none; 
            display: block;" 
     alt="Logo watermark" />
    @endif

    <div style="position: relative; z-index: 1;">
    <h1>Receivables Aging Report</h1>
    <p>As of: {{ $data['asOf'] }}</p>
    <table>
        <thead>
            <tr>
                <th class="text-left">Client</th>
                <th>0–30 days</th>
                <th>31–60 days</th>
                <th>61–90 days</th>
                <th>90+ days</th>
                <th>Total Receivable</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['agingData'] as $row)
            <tr class="{{ $row['client'] === 'TOTAL' ? 'total-row' : '' }}">
                <td class="text-left">{{ $row['client'] }}</td>
                <td>{{ number_format($row['bucket_0_30'], 2) }}</td>
                <td>{{ number_format($row['bucket_31_60'], 2) }}</td>
                <td>{{ number_format($row['bucket_61_90'], 2) }}</td>
                <td>{{ number_format($row['bucket_90_plus'], 2) }}</td>
                <td>{{ number_format($row['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top: 20px; font-size: 10px; color: #888;">Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>