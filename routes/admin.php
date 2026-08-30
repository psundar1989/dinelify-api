<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\AdminSessionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminSessionController::class, 'create'])->name('login');
        Route::post('login', [AdminSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminSessionController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('root');
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::middleware('admin.permission:manage-users')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        });

        Route::middleware('admin.permission:manage-locations')->group(function () {
            Route::resource('locations', LocationController::class)->except(['show']);
        });

        Route::middleware('admin.permission:manage-rooms')->group(function () {
            Route::resource('locations.rooms', RoomController::class)->except(['show', 'create', 'edit']);
        });

        Route::middleware('admin.permission:manage-menus')->group(function () {
            Route::resource('menus', MenuController::class)->except(['show']);
            Route::patch('menus/{menu}/publish', [MenuController::class, 'publish'])->name('menus.publish');
            Route::patch('menus/{menu}/disable', [MenuController::class, 'disable'])->name('menus.disable');
            Route::post('menus/{menu}/meals', [MenuController::class, 'storeMeal'])->name('menus.meals.store');
            Route::put('menus/{menu}/meals/{meal}', [MenuController::class, 'updateMeal'])->name('menus.meals.update');
            Route::delete('menus/{menu}/meals/{meal}', [MenuController::class, 'destroyMeal'])->name('menus.meals.destroy');
        });

        Route::middleware('admin.permission:manage-orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        });

        Route::middleware('admin.permission:view-reports')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export')
                ->where('type', 'csv|xlsx|pdf');
        });

        Route::middleware('admin.permission:manage-admin-users')->group(function () {
            Route::resource('admin-users', AdminUserController::class)->except(['show']);
            Route::patch('admin-users/{adminUser}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin-users.toggle-status');
        });

        Route::middleware('admin.permission:manage-roles')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
        });

        Route::middleware('admin.permission:view-audit-logs')->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });
    });
});
