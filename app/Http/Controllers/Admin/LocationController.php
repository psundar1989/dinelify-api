<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('admin.locations.index', [
            'locations' => Location::query()->withCount(['rooms', 'users'])->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.locations.form', ['location' => new Location]);
    }

    public function store(Request $request): RedirectResponse
    {
        Location::query()->create($this->validated($request));

        return redirect()->route('admin.locations.index')->with('status', 'Location created successfully.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.form', ['location' => $location]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $location->update($this->validated($request));

        return redirect()->route('admin.locations.index')->with('status', 'Location updated successfully.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->update(['status' => 'inactive']);

        return redirect()->route('admin.locations.index')->with('status', 'Location disabled.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
