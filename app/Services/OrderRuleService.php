<?php

namespace App\Services;

use App\Exceptions\OrderRuleException;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;

/**
 * Single source of truth for order business rules (advance-order window,
 * edit cutoff, availability). Consulted by both the menu and order
 * endpoints so Flutter never has to hardcode or duplicate these rules.
 */
class OrderRuleService
{
    public function advanceOrderDays(): int
    {
        return (int) Setting::get('advance_order_days', 5);
    }

    public function cutoffTime(): string
    {
        return (string) Setting::get('order_cutoff_time', '20:00');
    }

    public function allowOrderEdit(): bool
    {
        return (bool) (int) Setting::get('allow_order_edit', 1);
    }

    public function isWithinAdvanceWindow(Carbon $orderDate): bool
    {
        $today = Carbon::today();

        return $orderDate->copy()->startOfDay()->greaterThanOrEqualTo($today)
            && $orderDate->copy()->startOfDay()->lessThanOrEqualTo($today->copy()->addDays($this->advanceOrderDays()));
    }

    /**
     * The moment an order for $orderDate becomes locked: order_cutoff_time
     * on the day before order_date (e.g. cutoff 20:00 means tomorrow's
     * meals must be finalized by 8pm today).
     */
    public function cutoffAt(Carbon $orderDate): Carbon
    {
        [$hour, $minute] = array_pad(explode(':', $this->cutoffTime()), 2, 0);

        return $orderDate->copy()->subDay()->setTime((int) $hour, (int) $minute, 0);
    }

    public function isPastCutoff(Carbon $orderDate): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->cutoffAt($orderDate));
    }

    public function isOrderable(Carbon $orderDate): bool
    {
        return $this->isWithinAdvanceWindow($orderDate) && ! $this->isPastCutoff($orderDate);
    }

    public function canEditOrder(Order $order): bool
    {
        if (! $this->allowOrderEdit()) {
            return false;
        }

        return ! $this->isPastCutoff(Carbon::parse($order->order_date));
    }

    /**
     * @throws OrderRuleException
     */
    public function assertOrderable(Carbon $orderDate): void
    {
        if (! $this->isWithinAdvanceWindow($orderDate)) {
            throw new OrderRuleException(
                "Orders can only be placed up to {$this->advanceOrderDays()} day(s) in advance."
            );
        }

        if ($this->isPastCutoff($orderDate)) {
            throw new OrderRuleException('Order cutoff time has passed');
        }
    }

    /**
     * @throws OrderRuleException
     */
    public function assertEditable(Order $order): void
    {
        if (! $this->canEditOrder($order)) {
            throw new OrderRuleException('Order cutoff time has passed');
        }
    }

    /**
     * @param  array<int, array{meal_type: string, food_type: string}>  $selections
     *
     * @throws OrderRuleException
     */
    public function assertSelectionsAvailable(Menu $menu, array $selections): void
    {
        $availableMeals = $menu->meals()
            ->where('status', 'available')
            ->get()
            ->keyBy(fn ($meal) => "{$meal->meal_type}:{$meal->food_type}");

        foreach ($selections as $selection) {
            if ($selection['food_type'] === 'skip') {
                continue;
            }

            $key = "{$selection['meal_type']}:{$selection['food_type']}";

            if (! $availableMeals->has($key)) {
                throw new OrderRuleException(
                    "The selected {$selection['food_type']} {$selection['meal_type']} is not available for this date."
                );
            }
        }
    }

    /**
     * @throws OrderRuleException
     */
    public function findOrderableMenu(Carbon $orderDate): Menu
    {
        $menu = Menu::query()
            ->whereDate('menu_date', $orderDate->format('Y-m-d'))
            ->where('status', 'published')
            ->first();

        if (! $menu) {
            throw new OrderRuleException('No menu is available for the selected date.');
        }

        return $menu;
    }
}
