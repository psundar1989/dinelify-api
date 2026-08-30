<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user('admin');
        $allowedLocationIds = $admin->isTeamLead() ? $admin->locations()->pluck('locations.id') : null;

        $orders = Order::query()
            ->with(['user.location', 'user.room', 'orderDetails'])
            ->when($allowedLocationIds, fn ($q) => $q->whereHas('user', fn ($q2) => $q2->whereIn('location_id', $allowedLocationIds)))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('order_date', $request->string('date')))
            ->when($request->filled('location_id'), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('location_id', $request->integer('location_id'))))
            ->when($request->filled('room_id'), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('room_id', $request->integer('room_id'))))
            ->when($request->filled('meal_type'), fn ($q) => $q->whereHas('orderDetails', fn ($q2) => $q2->where('meal_type', $request->string('meal_type'))))
            ->when($request->filled('food_type'), fn ($q) => $q->whereHas('orderDetails', fn ($q2) => $q2->where('food_type', $request->string('food_type'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('order_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'locations' => $allowedLocationIds
                ? Location::query()->whereIn('id', $allowedLocationIds)->orderBy('name')->get()
                : Location::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['user.location', 'user.room', 'orderDetails']),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,locked,cancelled'],
        ]);

        $order->update($data);

        return redirect()->back()->with('status', 'Order status updated.');
    }
}
