<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with('orderDetails')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('order_date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('order_date', '<=', $request->string('to')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('order_date')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success($orders->through(fn ($order) => new OrderResource($order)), 'OK');
    }

    public function upcoming(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with('orderDetails')
            ->whereDate('order_date', '>=', Carbon::today()->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->orderBy('order_date')
            ->get();

        return ApiResponse::success(OrderResource::collection($orders), 'OK');
    }

    public function history(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with('orderDetails')
            ->whereDate('order_date', '<', Carbon::today()->format('Y-m-d'))
            ->orderByDesc('order_date')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success($orders->through(fn ($order) => new OrderResource($order)), 'OK');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            $request->user(),
            Carbon::parse($request->string('order_date')->toString()),
            $request->array('selections')
        );

        return ApiResponse::success(new OrderResource($order), 'Order created successfully', 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return ApiResponse::success(new OrderResource($order->load('orderDetails')), 'OK');
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order = $this->orderService->updateOrder($order, $request->array('selections'));

        return ApiResponse::success(new OrderResource($order), 'Order updated successfully');
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        $this->orderService->cancelOrder($order);

        return ApiResponse::success(null, 'Order cancelled successfully');
    }
}
