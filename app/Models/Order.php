<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $fillable = ['code', 'date', 'customer_id', 'amount_to_collect'];

    public function customer() 
    {
        return $this->belongsTo(Customer::class);
    }
}
