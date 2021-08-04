<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // prevent n+1 queries
        $this->load('customer', 'items.product');

        return [
            "id" => $this->id,
            "code" => $this->code,
            "date" => $this->date->format('Y-m-d'),
            "customer" => new CustomerResource($this->customer),
            "amount_to_collect" => $this->amount_to_collect,
            "promo" => new PromoResource($this->promo),
            "items" => $this->items->map(fn($value) => new OrderItemResource($value)),
        ];
    }
}
