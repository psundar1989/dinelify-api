<x-admin.app-layout title="Menus">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-md border-slate-300 shadow-sm text-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
            </select>
        </form>
        <a href="{{ route('admin.menus.create') }}" class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">+ New Menu</a>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Meals</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($menus as $menu)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $menu->menu_date->format('D, d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $menu->meals_count }}</td>
                            <td class="px-5 py-3">
                                <x-admin.badge :color="['draft' => 'amber', 'published' => 'emerald', 'disabled' => 'slate'][$menu->status]">{{ ucfirst($menu->status) }}</x-admin.badge>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <a href="{{ route('admin.menus.edit', $menu) }}" class="text-emerald-700 hover:underline">Manage</a>
                                @if ($menu->status !== 'published')
                                    <form method="POST" action="{{ route('admin.menus.publish', $menu) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-emerald-700 hover:underline">Publish</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.menus.disable', $menu) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-slate-500 hover:underline">Disable</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" class="inline" onsubmit="return confirm('Delete this menu and all its meals?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">No menus found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <div class="mt-4">{{ $menus->links() }}</div>
</x-admin.app-layout>
