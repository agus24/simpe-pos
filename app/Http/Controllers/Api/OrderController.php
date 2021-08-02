<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\JsonErrorResource;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateApi($request, [
            "date" => "required|date",
            "customer_id" => "required|exists:customers,id",
            'items' => 'required|array',
            "items.*.product_id" => "required|exists:products,id",
            "items.*.quantity" => "required|numeric|min:1"
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }

        $order = Order::create([
            "code" => Order::getUniqueOrderCode(),
            "date" => $request->date,
            "customer_id" => $request->customer_id,
        ]);

        $order->items()->insert(
            collect($request->items)->map(function($value) use ($order) {
                return [
                    "order_id" => $order->id,
                    "product_id" => $value['product_id'],
                    "quantity" => $value['quantity'],
                    "created_at" => now()
                ];
            })->toArray()
        );

        $order->amount_to_collect = $order->items->map(fn($value) => $value->product->price * $value->quantity)->sum();
        $order->save();

        return new OrderResource($order);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);

        if (! $order) {
            return new JsonErrorResource(message: "Order not found.");
        }

        return new OrderResource($order);
    }
}
