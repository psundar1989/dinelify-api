<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $orders) {}

    public function headings(): array
    {
        return ['Order Date', 'User', 'Mobile', 'Location', 'Room', 'Meal Type', 'Food Type', 'Order Status'];
    }

    public function collection(): Collection
    {
        return $this->orders->flatMap(function ($order) {
            return $order->orderDetails->map(fn ($detail) => [
                $order->order_date->format('Y-m-d'),
                $order->user->name,
                $order->user->mobile,
                $order->user->location?->name,
                $order->user->room?->room_number,
                $detail->meal_type,
                $detail->food_type,
                $order->status,
            ]);
        });
    }
}
