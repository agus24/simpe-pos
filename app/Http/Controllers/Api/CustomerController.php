<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\BasicJsonResource;
use App\Http\Resources\JsonErrorResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Customer::all()->map(function($value) {
            return new CustomerResource($value);
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateApi($request, [
            "name" => "required",
            "phone_number" => "required|unique:customers,phone_number",
            "address" => "required"
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }

        return new CustomerResource(
            Customer::create($request->only('name', 'phone_number', 'address'))
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
        $customer = Customer::find($id);
        if (!$customer) {
            return new JsonErrorResource(message: "customer_not_found");
        }

        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return new JsonErrorResource(message: "customer_not_found");
        }

        $validator = $this->validateApi($request, [
            "name" => "required",
            "phone_number" => "required|unique:customers,phone_number,{$customer->id}",
            "address" => "required"
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }
        
        $customer->update($request->only('name', 'phone_number', 'address'));

        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return new JsonErrorResource(message: "customer_not_found");
        }

        $customer->delete();

        return new BasicJsonResource(message: "Customer data deleted.");
    }
}
