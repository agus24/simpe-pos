<?php

namespace Tests\Feature\Model;

use Tests\TestCase;
use App\Models\Promo;
use App\Models\Product;
use App\Enums\Promo\Status;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_promo_is_valid()
    {
        $product = Product::factory()->create();
        $product->price = 50_000;
        $product->save();

        $promo = Promo::create([
            "name" => "test",
            "code" => "tests",
            "discount_percentage" => 12,
            "minimum_price" => 100_000,
            "status" => Status::Inactive,
        ]);

        $request = [
            "date" => now()->format('Y-m-d'),
            "promo_id" => 1,
            "items" => [
                [
                    "product_id" => $product->id,
                    "quantity" => 1
                ]
            ]
        ];

        // status inactive
        $this->assertFalse(Promo::isValid($request));

        $promo->status = Status::Active;
        $promo->save();

        // should false because not met minimum price
        $this->assertFalse(Promo::isValid($request));

        $request['items'][0]['quantity'] = 2;

        // should true because met minimum price
        $this->assertTrue(Promo::isValid($request));
    }
}
