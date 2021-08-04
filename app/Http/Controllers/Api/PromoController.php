<?php

namespace App\Http\Controllers\Api;

use App\Models\Promo;
use App\Models\Customer;
use App\Enums\Promo\Status;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\PromoResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\BasicJsonResource;
use App\Http\Resources\JsonErrorResource;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return [
            "data" => Promo::all()->map(fn($value) => new PromoResource($value))
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
            "name" => "required",
            "code" => "required|unique:promos,code",
            "discount_percentage" => "required|numeric",
            "minimum_price" => "required|numeric",
            "status" => ["required", "numeric", Rule::in([Status::Active, Status::Inactive])],
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }

        return new PromoResource(
            Promo::create($request->only("name", "code", "discount_percentage", "minimum_price", "status"))
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
        $promo = Promo::find($id);
        if (!$promo) {
            return new JsonErrorResource(message: "promo_not_found");
        }

        return new PromoResource($promo);
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
        $promo = promo::find($id);
        if (!$promo) {
            return new JsonErrorResource(message: "promo_not_found");
        }

        $validator = $this->validateApi($request, [
            "name" => "required",
            "code" => "required|unique:promos,code,{$promo->id}",
            "discount_percentage" => "required|numeric",
            "minimum_price" => "required|numeric",
            "status" => ["required", "numeric", Rule::in([Status::Active, Status::Inactive])],
        ]);

        if ($validator->fails()) {
            return new JsonErrorResource(data: $validator->getMessageBag()->toArray());
        }

        // prevent racing condition
        Cache::lock("promo:{$promo->id}")->get(function() use ($promo, $request) {
            $promo->update($request->only("name", "code", "discount_percentage", "minimum_price", "status"));
        });

        return new PromoResource($promo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $promo = Promo::find($id);
        if (!$promo) {
            return new JsonErrorResource(message: "promo_not_found");
        }

        // prevent racing condition
        Cache::lock("promo:{$promo->id}")->get(function() use ($promo) {
            $promo->delete();
        });

        return new BasicJsonResource(message: "promo data deleted.");
    }
}
