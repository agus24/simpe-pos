<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "discount_percentage" => $this->discount_percentage,
            "minimum_price" => $this->minimum_price,
            "status" => $this->status,
            "status_text" => $this->status->description
        ];
    }
}
