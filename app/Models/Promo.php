<?php

namespace App\Models;

use App\Enums\Promo\Status;
use Illuminate\Http\Request;
use BenSampo\Enum\Traits\CastsEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promo extends Model
{
    use HasFactory, CastsEnums;

    public $fillable = ["name", "code", "discount_percentage", "minimum_price", "status"];

    public $casts = [
        "status" => Status::class
    ];

    public function order() 
    {
        return $this->hasOne(Order::class);
    }

    public function isValid(array $request): bool
    {
        if ($this->status->value != Status::Active) return false;

        $items = collect($request['items']);

        $productMapping = Product::whereIn('id', $items->pluck('product_id'))->get()
            ->mapWithKeys(fn($product) => [$product->id => $product->price]);

        $totalPrice = $items->reduce(fn($carry, $item) => $item['quantity'] * $productMapping[$item['product_id']] + $carry);

        if ($totalPrice < $this->minimum_price) return false;

        return true;
    }
}
