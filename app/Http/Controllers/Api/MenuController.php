<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\MenuService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menuService) {}

    public function weekly(Request $request): JsonResponse
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->toString())
            : Carbon::today();

        return ApiResponse::success([
            'days' => $this->menuService->weekly($start),
        ], 'OK');
    }

    public function day(string $date): JsonResponse
    {
        return ApiResponse::success(
            $this->menuService->dayPayload(Carbon::parse($date)),
            'OK'
        );
    }

    public function index(Request $request): JsonResponse
    {
        $menus = Menu::query()
            ->with('meals')
            ->orderBy('menu_date')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success($menus, 'OK');
    }
}
