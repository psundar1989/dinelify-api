<x-admin.app-layout title="Users">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or mobile"
                   class="rounded-md border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select name="location_id" class="rounded-md border-slate-300 shadow-sm text-sm">
                <option value="">All locations</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-md border-slate-300 shadow-sm text-sm">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button class="rounded-md bg-slate-800 text-white text-sm px-3 py-2">Filter</button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">+ New User</a>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Mobile</th>
                        <th class="px-5 py-3">Location / Room</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $user->mobile }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $user->location?->name }} / {{ $user->room?->room_number }}</td>
                            <td class="px-5 py-3">
                                <x-admin.badge :color="$user->status === 'active' ? 'emerald' : 'slate'">{{ ucfirst($user->status) }}</x-admin.badge>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-700 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-slate-500 hover:underline">{{ $user->status === 'active' ? 'Disable' : 'Enable' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <div class="mt-4">{{ $users->links() }}</div>
</x-admin.app-layout>
