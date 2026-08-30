<x-admin.app-layout :title="'Menu · '.$menu->menu_date->format('d M Y')">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-admin.card title="Menu Details">
            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="space-y-4">
                @csrf @method('PUT')
                <x-admin.input label="Menu Date" name="menu_date" type="date" :value="$menu->menu_date->format('Y-m-d')" required />
                <x-admin.select label="Status" name="status" required :options="['draft' => 'Draft', 'published' => 'Published', 'disabled' => 'Disabled']" :selected="$menu->status" />
                <button class="rounded-md bg-emerald-600 text-white text-sm px-4 py-2 font-medium hover:bg-emerald-700">Save</button>
            </form>
        </x-admin.card>

        <x-admin.card title="Add Meal">
            <form method="POST" action="{{ route('admin.menus.meals.store', $menu) }}" class="space-y-4">
                @csrf
                <x-admin.select label="Meal Type" name="meal_type" required :options="['breakfast' => 'Breakfast', 'lunch' => 'Lunch', 'dinner' => 'Dinner']" />
                <x-admin.select label="Food Type" name="food_type" required :options="['veg' => 'Veg', 'non_veg' => 'Non-Veg']" />
                <x-admin.input label="Food Name" name="food_name" required />
                <x-admin.select label="Status" name="status" required :options="['available' => 'Available', 'unavailable' => 'Unavailable']" selected="available" />
                <button class="rounded-md bg-slate-800 text-white text-sm px-4 py-2 font-medium">Add Meal</button>
            </form>
        </x-admin.card>
    </div>

    <x-admin.card title="Meals" class="mt-4">
        @foreach ($meals as $meal)
            <form id="meal-form-{{ $meal->id }}" method="POST" action="{{ route('admin.menus.meals.update', [$menu, $meal]) }}" class="hidden">
                @csrf @method('PUT')
                <input type="hidden" name="meal_type" value="{{ $meal->meal_type }}">
                <input type="hidden" name="food_type" value="{{ $meal->food_type }}">
            </form>
            <form id="meal-delete-form-{{ $meal->id }}" method="POST" action="{{ route('admin.menus.meals.destroy', [$menu, $meal]) }}" class="hidden"
                  onsubmit="return confirm('Remove this meal?');">
                @csrf @method('DELETE')
            </form>
        @endforeach

        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Meal Type</th>
                        <th class="px-5 py-3">Food Type</th>
                        <th class="px-5 py-3">Food Name</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($meals as $meal)
                        <tr>
                            <td class="px-5 py-3 capitalize">{{ $meal->meal_type }}</td>
                            <td class="px-5 py-3 capitalize">{{ str_replace('_', '-', $meal->food_type) }}</td>
                            <td class="px-5 py-3">
                                <input type="text" name="food_name" value="{{ $meal->food_name }}" form="meal-form-{{ $meal->id }}"
                                       class="rounded-md border-slate-300 shadow-sm text-sm w-full">
                            </td>
                            <td class="px-5 py-3">
                                <select name="status" form="meal-form-{{ $meal->id }}" class="rounded-md border-slate-300 shadow-sm text-sm">
                                    <option value="available" @selected($meal->status === 'available')>Available</option>
                                    <option value="unavailable" @selected($meal->status === 'unavailable')>Unavailable</option>
                                </select>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <button type="submit" form="meal-form-{{ $meal->id }}" class="text-emerald-700 hover:underline">Save</button>
                                <button type="submit" form="meal-delete-form-{{ $meal->id }}" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No meals added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin.app-layout>
