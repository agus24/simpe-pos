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

    public static function isValid(array $request): bool
    {
        $promo = self::where('status', Status::Active)->where("id", $request['promo_id'])->first();
        if (!$promo) return false;

        $totalPrice = 0;
        foreach($request['items'] as $item) {
            $product = Product::where('id', $item['product_id'])->first();
            if (!$product) return false;

            $totalPrice += $product->price * $item['quantity'];
        }

        if ($totalPrice < $promo->minimum_price) return false;

        return true;
    }
}
