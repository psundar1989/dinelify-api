<x-admin.app-layout :title="$user->exists ? 'Edit User' : 'New User'">
    <x-admin.card>
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4 max-w-xl"
              x-data="{
                  locationId: '{{ old('location_id', $user->location_id) }}',
                  roomId: '{{ old('room_id', $user->room_id) }}',
                  rooms: @js($rooms->map(fn ($room) => ['id' => $room->id, 'room_number' => $room->room_number, 'location_id' => $room->location_id])->values()),
              }">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <x-admin.input label="Full Name" name="name" :value="$user->name" required />
            <x-admin.input label="Mobile Number" name="mobile" :value="$user->mobile" required />

            <x-admin.select label="Location" name="location_id" placeholder="Select a location" required
                :options="$locations->pluck('name', 'id')" :selected="$user->location_id"
                id="location_id" x-model="locationId" x-on:change="roomId = ''" />

            <div>
                <label for="room_id" class="block text-sm font-medium text-slate-700">Room</label>
                <select id="room_id" name="room_id" required x-model="roomId"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    <option value="">Select a room</option>
                    <template x-for="room in rooms.filter(r => String(r.location_id) === String(locationId))" :key="room.id">
                        <option :value="room.id" x-text="room.room_number"></option>
                    </template>
                </select>
                @error('room_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-admin.select label="Status" name="status" required :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$user->status ?? 'active'" />

            <div class="pt-2">
                <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Save User</button>
                <a href="{{ route('admin.users.index') }}" class="ml-2 text-sm text-slate-500">Cancel</a>
            </div>
        </form>
    </x-admin.card>
</x-admin.app-layout>
