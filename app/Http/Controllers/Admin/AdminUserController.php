<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.admin-users.index', [
            'adminUsers' => AdminUser::query()->with('roles')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.admin-users.form', [
            'adminUser' => new AdminUser,
            'roles' => Role::query()->orderBy('name')->get(),
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,name'],
            'location_ids' => ['array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
        ]);

        $adminUser = AdminUser::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
            'email_verified_at' => now(),
        ]);

        $adminUser->syncRoles([$data['role']]);
        $adminUser->locations()->sync($data['location_ids'] ?? []);

        return redirect()->route('admin.admin-users.index')->with('status', 'Admin user created successfully.');
    }

    public function edit(AdminUser $adminUser): View
    {
        return view('admin.admin-users.form', [
            'adminUser' => $adminUser->load('roles', 'locations'),
            'roles' => Role::query()->orderBy('name')->get(),
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admin_users,email,'.$adminUser->id],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,name'],
            'location_ids' => ['array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
        ]);

        $adminUser->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $adminUser->syncRoles([$data['role']]);
        $adminUser->locations()->sync($data['location_ids'] ?? []);

        return redirect()->route('admin.admin-users.index')->with('status', 'Admin user updated successfully.');
    }

    public function destroy(AdminUser $adminUser): RedirectResponse
    {
        $adminUser->update(['status' => 'inactive']);

        return redirect()->route('admin.admin-users.index')->with('status', 'Admin user disabled.');
    }

    public function toggleStatus(AdminUser $adminUser): RedirectResponse
    {
        $adminUser->update(['status' => $adminUser->status === 'active' ? 'inactive' : 'active']);

        return redirect()->back()->with('status', 'Admin user status updated.');
    }
}
