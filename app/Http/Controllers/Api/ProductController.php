<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\BasicJsonResource;
use App\Http\Resources\JsonErrorResource;

class ProductController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return [
            "data" => Product::all()->map(function($value) {
                return new ProductResource($value);
            })
        ];
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
            "code" => "required|unique:products,code",
            "name" => "required",
            "price" => "required|numeric|min:0"
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }

        return new ProductResource(
            Product::create($request->only('code', 'name', 'price'))
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
        $product = Product::find($id);
        if (!$product) {
            return new JsonErrorResource(message: "product_not_found");
        }

        return new ProductResource($product);
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
        $product = Product::find($id);
        if (!$product) {
            return new JsonErrorResource(message: "product_not_found");
        }

        $validator = $this->validateApi($request, [
            "code" => "required|unique:products,code,{$product->id}",
            "name" => "required",
            "price" => "required|numeric|min:0"
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }
        
        $product->update($request->only('code', 'name', 'price'));

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return new JsonErrorResource(message: "product_not_found");
        }

        $product->delete();

        return new BasicJsonResource(message: "Product data deleted.");
    }
}
