@php $currentRole = $adminUser->exists ? $adminUser->roles->pluck('name')->first() : null; @endphp
<x-admin.app-layout :title="$adminUser->exists ? 'Edit Admin User' : 'New Admin User'">
    <x-admin.card>
        <form method="POST" action="{{ $adminUser->exists ? route('admin.admin-users.update', $adminUser) : route('admin.admin-users.store') }}" class="space-y-4 max-w-xl">
            @csrf
            @if ($adminUser->exists) @method('PUT') @endif

            <x-admin.input label="Full Name" name="name" :value="$adminUser->name" required />
            <x-admin.input label="Email" name="email" type="email" :value="$adminUser->email" required />
            <x-admin.input label="Password" name="password" type="password" :required="!$adminUser->exists" />
            @if ($adminUser->exists)
                <p class="text-xs text-slate-500 -mt-3">Leave blank to keep the current password.</p>
            @endif

            <x-admin.select label="Role" name="role" required :options="$roles->pluck('name', 'name')" :selected="$currentRole" />
            <x-admin.select label="Status" name="status" required :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$adminUser->status ?? 'active'" />

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Assigned Locations (for Team Leads)</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($locations as $location)
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="location_ids[]" value="{{ $location->id }}"
                                   @checked($adminUser->exists && $adminUser->locations->contains('id', $location->id))
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            {{ $location->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Save Admin User</button>
                <a href="{{ route('admin.admin-users.index') }}" class="ml-2 text-sm text-slate-500">Cancel</a>
            </div>
        </form>
    </x-admin.card>
</x-admin.app-layout>
