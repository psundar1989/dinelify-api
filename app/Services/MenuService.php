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
        $rawMenu = Menu::query()
            ->whereDate('menu_date', $date->format('Y-m-d'))
            ->with('meals')
            ->first();

        $menu = $rawMenu?->status === 'published' ? $rawMenu : null;
        $isClosed = $rawMenu?->status === 'disabled';

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
            // A date is orderable purely by the past/cutoff/advance-window rules;
            // an admin-disabled menu also closes it. A date with no menu published
            // yet stays open (shows "No menu published yet" per meal) rather than
            // being locked — locking it would hide valid upcoming dates before the
            // admin gets around to publishing that day's menu.
            'is_orderable' => ! $isClosed && $this->rules->isOrderable($date),
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
