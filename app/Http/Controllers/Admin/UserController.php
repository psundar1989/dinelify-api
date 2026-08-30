<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['location', 'room'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->string('search')}%")
                    ->orWhere('mobile', 'like', "%{$request->string('search')}%");
            }))
            ->when($request->filled('location_id'), fn ($q) => $q->where('location_id', $request->integer('location_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'locations' => Location::query()->where('status', 'active')->orderBy('name')->get(),
            'rooms' => Room::query()->orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::query()->create($data);

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'locations' => Location::query()->orderBy('name')->get(),
            'rooms' => Room::query()->orderBy('room_number')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users.index')->with('status', 'User disabled.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return redirect()->back()->with('status', 'User status updated.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^[0-9]{7,15}$/', 'unique:users,mobile,'.($user?->id)],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
