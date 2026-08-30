<?php

namespace App\Services;

use App\Http\Resources\MealResource;
use App\Models\Menu;
use Carbon\Carbon;

class MenuService
{
    public function __construct(private readonly OrderRuleService $rules) {}

    public function dayPayload(Carbon $date): array
    {
        $menu = Menu::query()
            ->whereDate('menu_date', $date->format('Y-m-d'))
            ->where('status', 'published')
            ->with('meals')
            ->first();

        $grouped = ['breakfast' => [], 'lunch' => [], 'dinner' => []];

        foreach ($menu?->meals ?? [] as $meal) {
            $grouped[$meal->meal_type][] = $meal;
        }

        return [
            'date' => $date->format('Y-m-d'),
            'menus' => [
                'breakfast' => MealResource::collection($grouped['breakfast']),
                'lunch' => MealResource::collection($grouped['lunch']),
                'dinner' => MealResource::collection($grouped['dinner']),
            ],
            'is_orderable' => $menu !== null && $this->rules->isOrderable($date),
            'cutoff_at' => $this->rules->cutoffAt($date)->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array>
     */
    public function weekly(Carbon $start): array
    {
        return collect(range(0, 6))
            ->map(fn ($offset) => $this->dayPayload($start->copy()->addDays($offset)))
            ->all();
    }
}
