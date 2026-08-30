@php
    use App\Models\Order;
    use App\Models\OrderDetail;
    use App\Models\User;
    use Illuminate\Support\Carbon;

    $today = Carbon::today()->format('Y-m-d');
    $totalUsers = User::query()->where('status', 'active')->count();
    $todaysOrders = Order::query()->where('order_date', $today)->where('status', '!=', 'cancelled')->count();
    $upcomingOrders = Order::query()->where('order_date', '>', $today)->where('status', '!=', 'cancelled')->count();

    $counts = OrderDetail::query()
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('orders.order_date', $today)
        ->where('orders.status', '!=', 'cancelled')
        ->where('order_details.status', 'active')
        ->selectRaw('order_details.meal_type, order_details.food_type, count(*) as total')
        ->groupBy('order_details.meal_type', 'order_details.food_type')
        ->get();

    $mealCounts = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0];
    $vegCount = 0;
    $nonVegCount = 0;
    foreach ($counts as $row) {
        $mealCounts[$row->meal_type] = ($mealCounts[$row->meal_type] ?? 0) + $row->total;
        if ($row->food_type === 'veg') { $vegCount += $row->total; }
        if ($row->food_type === 'non_veg') { $nonVegCount += $row->total; }
    }
@endphp

<x-admin.app-layout title="Dashboard">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-admin.stat-tile label="Total Users" :value="$totalUsers" />
        <x-admin.stat-tile label="Today's Orders" :value="$todaysOrders" />
        <x-admin.stat-tile label="Upcoming Orders" :value="$upcomingOrders" />
        <x-admin.stat-tile label="Veg / Non-Veg Today" :value="$vegCount.' / '.$nonVegCount" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <x-admin.stat-tile label="Breakfast Count (today)" :value="$mealCounts['breakfast']" />
        <x-admin.stat-tile label="Lunch Count (today)" :value="$mealCounts['lunch']" />
        <x-admin.stat-tile label="Dinner Count (today)" :value="$mealCounts['dinner']" />
    </div>

    <x-admin.card title="Today's Meal Breakdown">
        <canvas id="mealChart" height="90"></canvas>
    </x-admin.card>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('mealChart'), {
                type: 'bar',
                data: {
                    labels: ['Breakfast', 'Lunch', 'Dinner'],
                    datasets: [
                        { label: 'Veg', backgroundColor: '#059669', data: [
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'breakfast' && $r->food_type === 'veg')->total ?? 0 }},
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'lunch' && $r->food_type === 'veg')->total ?? 0 }},
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'dinner' && $r->food_type === 'veg')->total ?? 0 }}
                        ]},
                        { label: 'Non-Veg', backgroundColor: '#b45309', data: [
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'breakfast' && $r->food_type === 'non_veg')->total ?? 0 }},
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'lunch' && $r->food_type === 'non_veg')->total ?? 0 }},
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'dinner' && $r->food_type === 'non_veg')->total ?? 0 }}
                        ]},
                        { label: 'Skip', backgroundColor: '#94a3b8', data: [
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'breakfast' && $r->food_type === 'skip')->total ?? 0 }},
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'lunch' && $r->food_type === 'skip')->total ?? 0 }},
                            {{ $counts->firstWhere(fn($r) => $r->meal_type === 'dinner' && $r->food_type === 'skip')->total ?? 0 }}
                        ]}
                    ]
                },
                options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
            });
        });
    </script>
</x-admin.app-layout>
