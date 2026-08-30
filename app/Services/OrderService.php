<?php

namespace App\Services;

use App\Exceptions\OrderRuleException;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private readonly OrderRuleService $rules) {}

    /**
     * @param  array<int, array{meal_type: string, food_type: string}>  $selections
     *
     * @throws OrderRuleException
     */
    public function createOrder(User $user, Carbon $orderDate, array $selections): Order
    {
        $this->rules->assertOrderable($orderDate);
        $menu = $this->rules->findOrderableMenu($orderDate);
        $this->rules->assertSelectionsAvailable($menu, $selections);

        $exists = Order::query()
            ->where('user_id', $user->id)
            ->whereDate('order_date', $orderDate->format('Y-m-d'))
            ->exists();

        if ($exists) {
            throw new OrderRuleException('An order already exists for this date. Use update instead.', 409);
        }

        return DB::transaction(function () use ($user, $orderDate, $selections) {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_date' => $orderDate->format('Y-m-d'),
                'status' => 'confirmed',
            ]);

            foreach ($selections as $selection) {
                $order->orderDetails()->create([
                    'meal_type' => $selection['meal_type'],
                    'food_type' => $selection['food_type'],
                    'status' => 'active',
                ]);
            }

            return $order->load('orderDetails');
        });
    }

    /**
     * @param  array<int, array{meal_type: string, food_type: string}>  $selections
     *
     * @throws OrderRuleException
     */
    public function updateOrder(Order $order, array $selections): Order
    {
        $this->rules->assertEditable($order);
        $menu = $this->rules->findOrderableMenu(Carbon::parse($order->order_date));
        $this->rules->assertSelectionsAvailable($menu, $selections);

        return DB::transaction(function () use ($order, $selections) {
            foreach ($selections as $selection) {
                $order->orderDetails()->updateOrCreate(
                    ['meal_type' => $selection['meal_type']],
                    ['food_type' => $selection['food_type'], 'status' => 'active']
                );
            }

            $order->update(['status' => 'confirmed']);

            return $order->fresh('orderDetails');
        });
    }

    /**
     * @throws OrderRuleException
     */
    public function cancelOrder(Order $order): Order
    {
        $this->rules->assertEditable($order);
        $order->update(['status' => 'cancelled']);

        return $order;
    }
}
