<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    public $fillable = ['code', 'date', 'customer_id', 'amount_to_collect', 'promo_id'];

    public $casts = [
        "date" => "date"
    ];

    public function customer() 
    {
        return $this->belongsTo(Customer::class);
    }

    public function items() 
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promo() 
    {
        return $this->belongsTo(Promo::class);
    }

    public static function getUniqueOrderCode() 
    {
        $random = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $randomize_order_code = substr(str_shuffle($random), 0, 7);
        while (Order::where('code', $randomize_order_code)->exists()) {
            $randomize_order_code = substr(str_shuffle($random), 0, 7);
        }

        return $randomize_order_code;
    }

    public function recalculateAmount()
    {
        $this->load('items');

        $amountToCollect = $this->items->reduce(function ($carry, $item) {
            return $carry + $item->product->price * $item->quantity;
        });

        $discount = $this->promo
            ? ($amountToCollect * $this->promo->discount_percentage / 100)
            : 0;

        $this->amount_to_collect = $amountToCollect - $discount;
        $this->save();
    }
}
