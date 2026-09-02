<x-admin.app-layout title="New Menu">
    <x-admin.card>
        <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-4 max-w-md">
            @csrf
            <x-admin.input label="Menu Date" name="menu_date" type="date" required />
            <x-admin.select label="Status" name="status" required :options="['draft' => 'Draft', 'disabled' => 'Disabled']" selected="draft" />
            <p class="text-xs text-slate-500">New menus start as Draft — add meals on the next screen, then publish from the menu list.</p>

            <div class="pt-2">
                <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Create Menu</button>
                <a href="{{ route('admin.menus.index') }}" class="ml-2 text-sm text-slate-500">Cancel</a>
            </div>
        </form>
    </x-admin.card>
</x-admin.app-layout>
