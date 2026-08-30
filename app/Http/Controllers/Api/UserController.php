<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Room;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $mobile = $request->string('mobile')->toString();
        $email = $request->filled('email') ? $request->string('email')->toString() : null;

        $room = Room::query()->findOrFail($request->integer('room_id'));
        if ($room->location_id !== $request->integer('location_id')) {
            throw new ApiException('The selected room does not belong to the selected location.');
        }

        $user = DB::transaction(function () use ($request, $mobile, $email) {
            return User::query()->create([
                'name' => $request->string('name')->toString(),
                'mobile' => $mobile,
                'email' => $email,
                'email_verified_at' => $email !== null ? now() : null,
                'location_id' => $request->integer('location_id'),
                'room_id' => $request->integer('room_id'),
                'status' => 'active',
            ]);
        });

        $token = $user->createToken('mobile-app')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user->load(['location', 'room'])),
        ], 'Registration successful', 201);
    }

    public function profile(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()->load(['location', 'room'])),
            'OK'
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('room_id')) {
            $room = Room::query()->findOrFail($request->integer('room_id'));
            $locationId = $request->integer('location_id') ?: $user->location_id;
            if ($room->location_id !== $locationId) {
                throw new ApiException('The selected room does not belong to the selected location.');
            }
        }

        $user->update($request->only(['name', 'location_id', 'room_id']));

        return ApiResponse::success(
            new UserResource($user->fresh(['location', 'room'])),
            'Profile updated successfully'
        );
    }
}
