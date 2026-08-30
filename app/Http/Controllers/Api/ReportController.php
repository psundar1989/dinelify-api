<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function daily(Request $request): JsonResponse
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : Carbon::today();

        return ApiResponse::success($this->reports->summarize($date, $date), 'OK');
    }

    public function weekly(Request $request): JsonResponse
    {
        $start = $request->filled('start_date') ? Carbon::parse($request->string('start_date')) : Carbon::today();

        return ApiResponse::success($this->reports->summarize($start, $start->copy()->addDays(6)), 'OK');
    }

    public function location(Request $request): JsonResponse
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : Carbon::today();

        if ($request->filled('location_id')) {
            return ApiResponse::success(
                $this->reports->summarize($date, $date, ['location_id' => $request->integer('location_id')]),
                'OK'
            );
        }

        return ApiResponse::success($this->reports->byLocation($date, $date), 'OK');
    }

    public function meal(Request $request): JsonResponse
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : Carbon::today();
        $mealType = $request->string('meal_type', 'breakfast')->toString();

        return ApiResponse::success($this->reports->byMeal($date, $date, $mealType), 'OK');
    }
}
