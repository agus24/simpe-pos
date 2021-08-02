<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
        $this->load('product');

        return [
            "product" => new ProductResource($this->product),
            "quantity" => $this->quantity
        ];
    }
}
