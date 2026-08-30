<?php

namespace App\Services;

use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @param  array{location_id?: int, room_id?: int, meal_type?: string, food_type?: string, status?: string}  $filters
     */
    public function summarize(Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->whereDate('orders.order_date', '>=', $from->format('Y-m-d'))
            ->whereDate('orders.order_date', '<=', $to->format('Y-m-d'))
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_details.status', 'active');

        $this->applyFilters($query, $filters);

        $rows = $query
            ->selectRaw('order_details.meal_type, order_details.food_type, count(*) as total')
            ->groupBy('order_details.meal_type', 'order_details.food_type')
            ->get();

        return $this->shape($rows);
    }

    public function byLocation(Carbon $from, Carbon $to): Collection
    {
        $rows = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('locations', 'locations.id', '=', 'users.location_id')
            ->whereDate('orders.order_date', '>=', $from->format('Y-m-d'))
            ->whereDate('orders.order_date', '<=', $to->format('Y-m-d'))
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_details.status', 'active')
            ->selectRaw('locations.id as location_id, locations.name as location_name, order_details.meal_type, order_details.food_type, count(*) as total')
            ->groupBy('locations.id', 'locations.name', 'order_details.meal_type', 'order_details.food_type')
            ->get();

        return $rows->groupBy('location_id')->map(fn ($group) => [
            'location_id' => $group->first()->location_id,
            'location_name' => $group->first()->location_name,
            'meals' => $this->shape($group),
        ])->values();
    }

    public function byRoom(Carbon $from, Carbon $to, ?int $locationId = null): Collection
    {
        $rows = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('rooms', 'rooms.id', '=', 'users.room_id')
            ->whereDate('orders.order_date', '>=', $from->format('Y-m-d'))
            ->whereDate('orders.order_date', '<=', $to->format('Y-m-d'))
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_details.status', 'active')
            ->when($locationId, fn ($q) => $q->where('users.location_id', $locationId))
            ->selectRaw('rooms.id as room_id, rooms.room_number, order_details.meal_type, order_details.food_type, count(*) as total')
            ->groupBy('rooms.id', 'rooms.room_number', 'order_details.meal_type', 'order_details.food_type')
            ->get();

        return $rows->groupBy('room_id')->map(fn ($group) => [
            'room_id' => $group->first()->room_id,
            'room_number' => $group->first()->room_number,
            'meals' => $this->shape($group),
        ])->values();
    }

    public function byMeal(Carbon $from, Carbon $to, string $mealType): array
    {
        return $this->summarize($from, $to, ['meal_type' => $mealType]);
    }

    /**
     * @param  array{location_id?: int, room_id?: int, meal_type?: string, food_type?: string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['location_id'])) {
            $query->where('users.location_id', $filters['location_id']);
        }

        if (! empty($filters['room_id'])) {
            $query->where('users.room_id', $filters['room_id']);
        }

        if (! empty($filters['meal_type'])) {
            $query->where('order_details.meal_type', $filters['meal_type']);
        }

        if (! empty($filters['food_type'])) {
            $query->where('order_details.food_type', $filters['food_type']);
        }
    }

    private function shape(Collection $rows): array
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        $foodTypes = ['veg', 'non_veg', 'skip'];

        $result = [];
        foreach ($mealTypes as $mealType) {
            $result[$mealType] = array_fill_keys($foodTypes, 0);
            $result[$mealType]['total'] = 0;
        }

        foreach ($rows as $row) {
            if (! isset($result[$row->meal_type])) {
                continue;
            }
            $result[$row->meal_type][$row->food_type] = (int) $row->total;
            $result[$row->meal_type]['total'] += (int) $row->total;
        }

        return $result;
    }
}
