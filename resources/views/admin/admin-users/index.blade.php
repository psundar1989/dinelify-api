<x-admin.app-layout title="Admin Users">
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.admin-users.create') }}" class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">+ New Admin User</a>
    </div>

    <x-admin.card>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($adminUsers as $admin)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $admin->name }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $admin->email }}</td>
                        <td class="px-5 py-3"><x-admin.badge color="blue">{{ $admin->roles->pluck('name')->join(', ') ?: '—' }}</x-admin.badge></td>
                        <td class="px-5 py-3"><x-admin.badge :color="$admin->status === 'active' ? 'emerald' : 'slate'">{{ ucfirst($admin->status) }}</x-admin.badge></td>
                        <td class="px-5 py-3 text-right space-x-2">
                            <a href="{{ route('admin.admin-users.edit', $admin) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.admin-users.toggle-status', $admin) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline">{{ $admin->status === 'active' ? 'Disable' : 'Enable' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No admin users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>

    <div class="mt-4">{{ $adminUsers->links() }}</div>
</x-admin.app-layout>
