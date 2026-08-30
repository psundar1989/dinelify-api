@php $assigned = $role->exists ? $role->permissions->pluck('name')->all() : []; @endphp
<x-admin.app-layout :title="$role->exists ? 'Edit Role' : 'New Role'">
    <x-admin.card>
        <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="space-y-4 max-w-xl">
            @csrf
            @if ($role->exists) @method('PUT') @endif

            <x-admin.input label="Role Name" name="name" :value="$role->name" required />

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Permissions</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($permissions as $permission)
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   @checked(in_array($permission->name, $assigned))
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Save Role</button>
                <a href="{{ route('admin.roles.index') }}" class="ml-2 text-sm text-slate-500">Cancel</a>
            </div>
        </form>
    </x-admin.card>
</x-admin.app-layout>
