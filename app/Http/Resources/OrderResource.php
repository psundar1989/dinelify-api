<?php

namespace App\Http\Resources;

use App\Services\OrderRuleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rules = app(OrderRuleService::class);
        $orderDate = Carbon::parse($this->order_date);

        return [
            'id' => $this->id,
            'order_date' => $this->order_date->format('Y-m-d'),
            'status' => $this->status,
            'is_editable' => $this->status !== 'cancelled' && $rules->canEditOrder($this->resource),
            'cutoff_at' => $rules->cutoffAt($orderDate)->toIso8601String(),
            'order_details' => OrderDetailResource::collection($this->whenLoaded('orderDetails')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
