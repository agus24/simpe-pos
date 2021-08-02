<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    public $fillable = ['order_id', 'product_id', 'quantity'];

    public function order() 
    {
        return $this->belongsTo(Order::class);
    }

    public function items() 
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}
