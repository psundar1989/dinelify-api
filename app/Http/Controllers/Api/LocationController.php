<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = Location::query()->where('status', 'active')->orderBy('name')->get();

        return ApiResponse::success(LocationResource::collection($locations), 'OK');
    }
}
