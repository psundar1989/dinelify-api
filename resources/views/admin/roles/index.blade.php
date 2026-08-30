<x-admin.app-layout title="Roles & Permissions">
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.roles.create') }}" class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">+ New Role</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach ($roles as $role)
            <x-admin.card :title="$role->name">
                <div class="flex flex-wrap gap-1 mb-4">
                    @forelse ($role->permissions as $permission)
                        <x-admin.badge color="blue">{{ $permission->name }}</x-admin.badge>
                    @empty
                        <span class="text-sm text-slate-400">No permissions assigned.</span>
                    @endforelse
                </div>
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-emerald-700 hover:underline">Edit</a>
                    @unless (in_array($role->name, ['super-admin', 'admin', 'team-lead']))
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    @endunless
                </div>
            </x-admin.card>
        @endforeach
    </div>
</x-admin.app-layout>
