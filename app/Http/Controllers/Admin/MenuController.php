<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $menus = Menu::query()
            ->withCount('meals')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('menu_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.menus.index', ['menus' => $menus]);
    }

    public function create(): View
    {
        return view('admin.menus.form', ['menu' => new Menu]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_date' => ['required', 'date_format:Y-m-d', 'unique:menus,menu_date'],
            'status' => ['required', 'in:draft,published,disabled'],
        ]);

        $menu = Menu::query()->create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu created. Now add meals.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', [
            'menu' => $menu,
            'meals' => $menu->meals()->orderBy('meal_type')->orderBy('food_type')->get(),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'menu_date' => ['required', 'date_format:Y-m-d', 'unique:menus,menu_date,'.$menu->id],
            'status' => ['required', 'in:draft,published,disabled'],
        ]);

        $menu->update($data);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('status', 'Menu deleted.');
    }

    public function publish(Menu $menu): RedirectResponse
    {
        $menu->update(['status' => 'published']);

        return redirect()->back()->with('status', 'Menu published.');
    }

    public function disable(Menu $menu): RedirectResponse
    {
        $menu->update(['status' => 'disabled']);

        return redirect()->back()->with('status', 'Menu disabled.');
    }

    public function storeMeal(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->mealValidation($request, $menu);

        $menu->meals()->create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Meal added successfully.');
    }

    public function updateMeal(Request $request, Menu $menu, Meal $meal): RedirectResponse
    {
        $data = $this->mealValidation($request, $menu, $meal);

        $meal->update($data);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Meal updated successfully.');
    }

    public function destroyMeal(Menu $menu, Meal $meal): RedirectResponse
    {
        $meal->delete();

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Meal removed.');
    }

    private function mealValidation(Request $request, Menu $menu, ?Meal $meal = null): array
    {
        return $request->validate([
            'meal_type' => [
                'required', 'in:breakfast,lunch,dinner',
                Rule::unique('meals', 'meal_type')
                    ->where('menu_id', $menu->id)
                    ->where('food_type', $request->input('food_type'))
                    ->ignore($meal?->id),
            ],
            'food_type' => ['required', 'in:veg,non_veg'],
            'food_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:available,unavailable'],
        ], [
            'meal_type.unique' => 'This menu already has a meal for that meal type + food type combination.',
        ]);
    }
}
