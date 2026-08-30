<x-admin.app-layout :title="'Order · '.$order->order_date->format('d M Y')">
    <x-admin.card title="Order Details" class="max-w-2xl">
        <dl class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div><dt class="text-slate-500">Order Date</dt><dd class="font-medium">{{ $order->order_date->format('d M Y') }}</dd></div>
            <div><dt class="text-slate-500">User</dt><dd class="font-medium">{{ $order->user->name }} ({{ $order->user->mobile }})</dd></div>
            <div><dt class="text-slate-500">Location</dt><dd class="font-medium">{{ $order->user->location?->name }}</dd></div>
            <div><dt class="text-slate-500">Room</dt><dd class="font-medium">{{ $order->user->room?->room_number }}</dd></div>
        </dl>

        <table class="min-w-full divide-y divide-slate-200 text-sm mb-6">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-2">Meal Type</th><th class="px-4 py-2">Selection</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td class="px-4 py-2 capitalize">{{ $detail->meal_type }}</td>
                        <td class="px-4 py-2 capitalize">{{ str_replace('_', '-', $detail->food_type) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="flex items-end gap-3">
            @csrf @method('PATCH')
            <x-admin.select label="Order Status" name="status" required
                :options="['pending' => 'Pending', 'confirmed' => 'Confirmed', 'locked' => 'Locked', 'cancelled' => 'Cancelled']" :selected="$order->status" />
            <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Update Status</button>
        </form>

        <a href="{{ route('admin.orders.index') }}" class="inline-block mt-4 text-sm text-slate-500">Back to orders</a>
    </x-admin.card>
</x-admin.app-layout>
