<x-admin.app-layout title="Locations">
    <div class="flex items-center justify-end mb-4">
        <a href="{{ route('admin.locations.create') }}" class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">+ New Location</a>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Rooms</th>
                        <th class="px-5 py-3">Users</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($locations as $location)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $location->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $location->rooms_count }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $location->users_count }}</td>
                            <td class="px-5 py-3">
                                <x-admin.badge :color="$location->status === 'active' ? 'emerald' : 'slate'">{{ ucfirst($location->status) }}</x-admin.badge>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <a href="{{ route('admin.locations.edit', $location) }}" class="text-emerald-700 hover:underline">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No locations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <div class="mt-4">{{ $locations->links() }}</div>
</x-admin.app-layout>
