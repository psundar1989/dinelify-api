<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
    <h1>Dinelify Orders Report</h1>
    <p>Generated {{ now()->format('d M Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Date</th><th>User</th><th>Mobile</th><th>Location</th><th>Room</th><th>Meal</th><th>Selection</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td>{{ $order->order_date->format('Y-m-d') }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->user->mobile }}</td>
                        <td>{{ $order->user->location?->name }}</td>
                        <td>{{ $order->user->room?->room_number }}</td>
                        <td>{{ ucfirst($detail->meal_type) }}</td>
                        <td>{{ str_replace('_', '-', $detail->food_type) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="8">No orders in range.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
