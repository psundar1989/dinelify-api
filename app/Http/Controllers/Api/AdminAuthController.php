<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\AdminUser;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Token-based admin login, kept separate from the admin panel's session
 * login. Used so external tools / the reports API can authenticate as an
 * admin without a browser session.
 */
class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $admin = AdminUser::query()->where('email', $request->string('email'))->first();

        if (! $admin || ! Hash::check($request->string('password'), $admin->password)) {
            return ApiResponse::error('Invalid credentials', null, 401);
        }

        if ($admin->status !== 'active') {
            return ApiResponse::error('This admin account has been disabled.', null, 403);
        }

        $token = $admin->createToken('admin-api', ['reports:view'])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'roles' => $admin->getRoleNames(),
            ],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }
}
