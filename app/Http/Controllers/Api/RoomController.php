<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['location_id' => ['required', 'integer', 'exists:locations,id']]);

        $rooms = Room::query()
            ->where('location_id', $request->integer('location_id'))
            ->where('status', 'active')
            ->orderBy('room_number')
            ->get();

        return ApiResponse::success(RoomResource::collection($rooms), 'OK');
    }
}
