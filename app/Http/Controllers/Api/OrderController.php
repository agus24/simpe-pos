<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Promo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\JsonErrorResource;
use App\Repositories\OrderRepository;

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
            'promo_id' => 'exists:promos,id|unique:orders,promo_id',
            "items.*.product_id" => "required|exists:products,id",
            "items.*.quantity" => "required|numeric|min:1"
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }

        $promo = Promo::find($request->promo_id);

        if ($promo && !$promo->isValid($request->all())) {
            return new JsonErrorResource(message: "Sorry, You cannot use this promo code.");
        }

        return new OrderResource(
            OrderRepository::init()->create($request->all())
        );
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
