<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['success' => true, 'message' => 'pong', 'data' => null]));

Route::prefix('auth')->group(function () {
    Route::post('send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:10,1');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    Route::post('login-by-mobile', [AuthController::class, 'loginByMobile'])->middleware('throttle:10,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

Route::post('/user/register', [UserController::class, 'register'])->middleware('throttle:10,1');
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/rooms', [RoomController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/weekly', [MenuController::class, 'weekly']);
    Route::get('/menus/{date}', [MenuController::class, 'day'])->where('date', '\d{4}-\d{2}-\d{2}');

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/upcoming', [OrderController::class, 'upcoming']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
});

Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'admin.permission:view-reports'])->prefix('reports')->group(function () {
    Route::get('/daily', [ReportController::class, 'daily']);
    Route::get('/weekly', [ReportController::class, 'weekly']);
    Route::get('/location', [ReportController::class, 'location']);
    Route::get('/meal', [ReportController::class, 'meal']);
});

Route::middleware(['auth:sanctum', 'admin.permission:manage-users'])->prefix('admin/users')->group(function () {
    Route::get('/', [AdminUserController::class, 'index']);
    Route::post('/', [AdminUserController::class, 'store']);
    Route::put('/{user}', [AdminUserController::class, 'update']);
    Route::delete('/{user}', [AdminUserController::class, 'destroy']);
    Route::patch('/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);
});

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth:sanctum');
