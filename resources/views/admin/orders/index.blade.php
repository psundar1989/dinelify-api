<x-admin.app-layout title="Orders">
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input type="date" name="date" value="{{ request('date') }}" class="rounded-md border-slate-300 shadow-sm text-sm">
        <select name="location_id" class="rounded-md border-slate-300 shadow-sm text-sm">
            <option value="">All locations</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
        <select name="meal_type" class="rounded-md border-slate-300 shadow-sm text-sm">
            <option value="">All meals</option>
            @foreach (['breakfast', 'lunch', 'dinner'] as $type)
                <option value="{{ $type }}" @selected(request('meal_type') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <select name="food_type" class="rounded-md border-slate-300 shadow-sm text-sm">
            <option value="">All food types</option>
            @foreach (['veg' => 'Veg', 'non_veg' => 'Non-Veg', 'skip' => 'Skip'] as $val => $label)
                <option value="{{ $val }}" @selected(request('food_type') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-slate-300 shadow-sm text-sm">
            <option value="">All statuses</option>
            @foreach (['pending', 'confirmed', 'locked', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-slate-800 text-white text-sm px-3 py-2">Filter</button>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 self-center">Reset</a>
    </form>

    <x-admin.card>
        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Location / Room</th>
                        <th class="px-5 py-3">Meals</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $order->order_date->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $order->user->name }} ({{ $order->user->mobile }})</td>
                            <td class="px-5 py-3 text-slate-600">{{ $order->user->location?->name }} / {{ $order->user->room?->room_number }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                @foreach ($order->orderDetails as $detail)
                                    <span class="block">{{ ucfirst($detail->meal_type) }}: {{ str_replace('_', '-', $detail->food_type) }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3">
                                <x-admin.badge :color="['pending' => 'amber', 'confirmed' => 'emerald', 'locked' => 'blue', 'cancelled' => 'red'][$order->status]">{{ ucfirst($order->status) }}</x-admin.badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-emerald-700 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin.app-layout>
