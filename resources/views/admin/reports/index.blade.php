@php $mealTypes = ['breakfast', 'lunch', 'dinner']; @endphp
<x-admin.app-layout title="Reports">
    <form method="GET" class="flex flex-wrap gap-2 mb-4 items-end">
        <div>
            <label class="block text-xs text-slate-500">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-md border-slate-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-md border-slate-300 shadow-sm text-sm">
        </div>
        <select name="location_id" class="rounded-md border-slate-300 shadow-sm text-sm">
            <option value="">All locations</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
        <select name="meal_type" class="rounded-md border-slate-300 shadow-sm text-sm">
            <option value="">All meals</option>
            @foreach ($mealTypes as $type)
                <option value="{{ $type }}" @selected(request('meal_type') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-slate-800 text-white text-sm px-3 py-2">Apply</button>

        <div class="ml-auto flex gap-2">
            <a href="{{ route('admin.reports.export', array_merge(['type' => 'csv'], request()->query())) }}" class="rounded-md ring-1 ring-slate-300 text-sm px-3 py-2">CSV</a>
            <a href="{{ route('admin.reports.export', array_merge(['type' => 'xlsx'], request()->query())) }}" class="rounded-md ring-1 ring-slate-300 text-sm px-3 py-2">Excel</a>
            <a href="{{ route('admin.reports.export', array_merge(['type' => 'pdf'], request()->query())) }}" class="rounded-md ring-1 ring-slate-300 text-sm px-3 py-2">PDF</a>
        </div>
    </form>

    <x-admin.card title="Meal Summary" class="mb-4">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-2">Meal</th><th class="px-4 py-2">Veg</th><th class="px-4 py-2">Non-Veg</th><th class="px-4 py-2">Skip</th><th class="px-4 py-2">Total</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($mealTypes as $type)
                    <tr>
                        <td class="px-4 py-2 capitalize font-medium">{{ $type }}</td>
                        <td class="px-4 py-2">{{ $summary[$type]['veg'] }}</td>
                        <td class="px-4 py-2">{{ $summary[$type]['non_veg'] }}</td>
                        <td class="px-4 py-2">{{ $summary[$type]['skip'] }}</td>
                        <td class="px-4 py-2 font-semibold">{{ $summary[$type]['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <x-admin.card title="By Location">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="px-2 py-2">Location</th><th class="px-2 py-2">Total Orders</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($byLocation as $row)
                        <tr><td class="px-2 py-2">{{ $row['location_name'] }}</td><td class="px-2 py-2">{{ collect($row['meals'])->sum('total') }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="px-2 py-4 text-center text-slate-400">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card title="By Room">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="px-2 py-2">Room</th><th class="px-2 py-2">Total Orders</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($byRoom as $row)
                        <tr><td class="px-2 py-2">{{ $row['room_number'] }}</td><td class="px-2 py-2">{{ collect($row['meals'])->sum('total') }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="px-2 py-4 text-center text-slate-400">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    </div>

    <x-admin.card title="User Order Report">
        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">User</th><th class="px-5 py-3">Location / Room</th><th class="px-5 py-3">Meals</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-5 py-3">{{ $order->order_date->format('d M Y') }}</td>
                            <td class="px-5 py-3">{{ $order->user->name }}</td>
                            <td class="px-5 py-3">{{ $order->user->location?->name }} / {{ $order->user->room?->room_number }}</td>
                            <td class="px-5 py-3">
                                @foreach ($order->orderDetails as $detail)
                                    <span class="block">{{ ucfirst($detail->meal_type) }}: {{ str_replace('_', '-', $detail->food_type) }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">No orders in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin.app-layout>
