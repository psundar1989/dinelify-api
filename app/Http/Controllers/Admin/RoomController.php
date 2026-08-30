<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function store(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $location->rooms()->create($data);

        return redirect()->route('admin.locations.edit', $location)->with('status', 'Room added successfully.');
    }

    public function update(Request $request, Location $location, Room $room): RedirectResponse
    {
        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $room->update($data);

        return redirect()->route('admin.locations.edit', $location)->with('status', 'Room updated successfully.');
    }

    public function destroy(Location $location, Room $room): RedirectResponse
    {
        $room->update(['status' => 'inactive']);

        return redirect()->route('admin.locations.edit', $location)->with('status', 'Room disabled.');
    }
}
