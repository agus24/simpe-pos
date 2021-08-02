<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $fillable = ['code', 'date', 'customer_id', 'amount_to_collect'];

    public $casts = [
        "date" => "date"
    ];

    public function customer() 
    {
        return $this->belongsTo(Customer::class);
    }

    public function items() 
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
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
}
