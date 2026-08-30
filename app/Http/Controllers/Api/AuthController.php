<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginByMobileRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $mobile = $request->string('mobile')->toString();

        $key = "send-otp:{$mobile}";
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return ApiResponse::error('Too many OTP requests. Please try again later.', null, 429);
        }
        RateLimiter::hit($key, 60);

        $result = $this->otpService->issue($mobile);

        return ApiResponse::success($result, 'OTP sent successfully');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $mobile = $request->string('mobile')->toString();

        $this->otpService->verify($mobile, $request->string('otp')->toString());

        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user) {
            return ApiResponse::success([
                'is_new_user' => true,
                'mobile' => $mobile,
            ], 'OTP verified. Please complete registration.');
        }

        if ($user->status !== 'active') {
            return ApiResponse::error('Your account has been disabled. Please contact support.', null, 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return ApiResponse::success([
            'is_new_user' => false,
            'token' => $token,
            'user' => new UserResource($user->load(['location', 'room'])),
        ], 'Login successful');
    }

    /**
     * Looks a user up by their registered mobile number and, on the
     * Dinelify website, this is all "Order Food" needs to resume an
     * existing account — no OTP or email step. Mirrors the existing-user
     * branch of verifyOtp() minus the OTP gate.
     */
    public function loginByMobile(LoginByMobileRequest $request): JsonResponse
    {
        $mobile = $request->string('mobile')->toString();

        $key = "login-by-mobile:{$mobile}";
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return ApiResponse::error('Too many attempts. Please try again later.', null, 429);
        }
        RateLimiter::hit($key, 60);

        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user) {
            return ApiResponse::success([
                'is_new_user' => true,
                'mobile' => $mobile,
            ], 'No account found for this mobile number. Please register.');
        }

        if ($user->status !== 'active') {
            return ApiResponse::error('Your account has been disabled. Please contact support.', null, 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return ApiResponse::success([
            'is_new_user' => false,
            'token' => $token,
            'user' => new UserResource($user->load(['location', 'room'])),
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()->load(['location', 'room'])),
            'OK'
        );
    }
}
