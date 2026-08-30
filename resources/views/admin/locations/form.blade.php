<x-admin.app-layout :title="$location->exists ? 'Manage Location' : 'New Location'">
    <div class="grid grid-cols-1 {{ $location->exists ? 'lg:grid-cols-2' : '' }} gap-4">
        <x-admin.card :title="$location->exists ? 'Location Details' : null">
            <form method="POST" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}" class="space-y-4">
                @csrf
                @if ($location->exists) @method('PUT') @endif

                <x-admin.input label="Location Name" name="name" :value="$location->name" required />
                <x-admin.select label="Status" name="status" required :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$location->status ?? 'active'" />

                <div class="pt-2">
                    <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Save Location</button>
                    <a href="{{ route('admin.locations.index') }}" class="ml-2 text-sm text-slate-500">Back</a>
                </div>
            </form>
        </x-admin.card>

        @if ($location->exists)
            <x-admin.card title="Rooms">
                <form method="POST" action="{{ route('admin.locations.rooms.store', $location) }}" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="room_number" placeholder="Room number" required
                           class="flex-1 rounded-md border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <input type="hidden" name="status" value="active">
                    <button class="rounded-md bg-slate-800 text-white text-sm px-3 py-2">Add</button>
                </form>

                <ul class="divide-y divide-slate-100">
                    @forelse ($location->rooms()->orderBy('room_number')->get() as $room)
                        <li class="py-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $room->room_number }}</span>
                            <div class="flex items-center gap-3">
                                <x-admin.badge :color="$room->status === 'active' ? 'emerald' : 'slate'">{{ ucfirst($room->status) }}</x-admin.badge>
                                <form method="POST" action="{{ route('admin.locations.rooms.update', [$location, $room]) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                    <input type="hidden" name="status" value="{{ $room->status === 'active' ? 'inactive' : 'active' }}">
                                    <button class="text-slate-500 hover:underline">{{ $room->status === 'active' ? 'Disable' : 'Enable' }}</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-slate-400 text-sm">No rooms yet.</li>
                    @endforelse
                </ul>
            </x-admin.card>
        @endif
    </div>
</x-admin.app-layout>
