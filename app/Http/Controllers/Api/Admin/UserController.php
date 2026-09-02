<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return ApiResponse::paginated(
            $users,
            UserResource::collection($users->items()),
            'Users retrieved successfully.',
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());

        return ApiResponse::success(
            new UserResource($user->load(['location', 'room'])),
            'User created successfully.',
            201
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return ApiResponse::success(
            new UserResource($user->fresh(['location', 'room'])),
            'User updated successfully.'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $user->update(['status' => 'inactive']);

        return ApiResponse::success(null, 'User disabled successfully.');
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return ApiResponse::success(
            new UserResource($user->fresh(['location', 'room'])),
            'User status updated successfully.'
        );
    }
}
