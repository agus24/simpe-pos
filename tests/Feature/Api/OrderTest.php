<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Promo;
use App\Models\Product;
use App\Models\Customer;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create()
    {
        $customer = Customer::factory()->create();

        $promo = Promo::factory()->create();
        $promo->minimum_price = 100_000;
        $promo->save();

        $product = Product::factory()->create();
        $product->price = 50_000;
        $product->save();

        $data = [];
        $response = $this->post(route('api.orders.store'), $data);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];

        $this->assertTrue(array_key_exists('date', $responseJson));
        $this->assertTrue(array_key_exists('customer_id', $responseJson));
        $this->assertTrue(array_key_exists('items', $responseJson));
        
        $items = [
            [
                "product_id" => $product->id,
                "quantity" => 1
            ]
        ];

        $data = [
            "date" => now()->format('Y-m-d'),
            "customer_id" => $customer->id,
            "items" => $items
        ];

        $response = $this->post(route('api.orders.store'), $data);
        $response->assertStatus(Response::HTTP_CREATED);
        $responseJson = $response->json()['data'];
        
        $this->assertEquals($responseJson['date'], $data['date']);
        $this->assertEquals($responseJson['customer']['id'], $data['customer_id']);
        $this->assertEquals($responseJson['amount_to_collect'], $product->price);
        $this->assertEquals($responseJson['items'][0]['product']['id'], $product->id);
        $this->assertEquals($responseJson['items'][0]['quantity'], $items[0]['quantity']);
        $this->assertEquals($responseJson['promo'], null);

        // with promo
        $data['promo_id'] = $promo->id;
        $response = $this->post(route('api.orders.store'), $data);
        $responseJson = $response->json()['data'];
        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $data['items'][0]['quantity'] = 2;
        $response = $this->post(route('api.orders.store'), $data);
        $responseJson = $response->json()['data'];
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals($responseJson['promo']['id'], $promo->id);
    }

    public function test_get_data() 
    {
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();

        $order = Order::create([
            "code" => "random_code",
            "customer_id" => $customer->id,
            "date" => now(),
            "amount_to_collect" => 100000
        ]);

        $order->items()->create([
            "product_id" => $product->id,
            "quantity" => 1
        ]);

        $response = $this->get(route('api.orders.show', ['order' => $order]));
        $response->assertStatus(Response::HTTP_OK);
        $responseJson = $response->json()['data'];

        $order->load('customer', 'items.product');
        $this->assertEquals($responseJson['code'], $order->code);
        $this->assertEquals($responseJson['date'], $order->date->format('Y-m-d'));
        $this->assertEquals($responseJson['customer']['name'], $order->customer->name);
        $this->assertEquals($responseJson['amount_to_collect'], $order->amount_to_collect);
        $this->assertEquals($responseJson['items'][0]['product']['name'], $order->items[0]->product->name);
        $this->assertEquals($responseJson['items'][0]['quantity'], $order->items[0]->quantity);

        $response = $this->get(route('api.orders.show', ['order' => -1]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];

        $this->assertEquals($responseJson['message'], "Order not found.");
    }
}
