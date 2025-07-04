<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'field_id' => $this->field_id,
            'start_time' => $this->start_time->toDateTimeString(),
            'end_time' => $this->end_time->toDateTimeString(),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
