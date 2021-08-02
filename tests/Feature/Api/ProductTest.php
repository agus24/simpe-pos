<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProductTest extends TestCase
{    
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all()
    {
        $products = Product::factory()->count(3)->create();
        $response = $this->get(route('api.products.index'));
        $response->assertStatus(Response::HTTP_OK);

        $responseJson = $response->json()['data'];

        $this->assertEquals(count($responseJson), 3);
        $this->assertEquals($responseJson[0]['name'], $products[0]->name);
        $this->assertEquals($responseJson[1]['name'], $products[1]->name);
        $this->assertEquals($responseJson[2]['name'], $products[2]->name);
    }

    public function test_create() 
    {
        // test with empty data
        $response = $this->post(route('api.products.store'), []);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        $this->assertTrue(array_key_exists('code', $responseJson));
        $this->assertTrue(array_key_exists('name', $responseJson));
        $this->assertTrue(array_key_exists('price', $responseJson));

        // success response
        $data = [
            "code" => "test",
            "name" => "12345",
            "price" => 100000.0
        ];

        $response = $this->post(route('api.products.store'), $data);
        $response->assertStatus(Response::HTTP_CREATED);
        $responseJson = $response->json()['data'];

        $this->assertEquals(Product::count(), 1);
        $this->assertEquals($responseJson['code'], $data['code']);
        $this->assertEquals($responseJson['name'], $data['name']);
        $this->assertEquals($responseJson['price'], $data['price']);

        // test duplicate code
        $response = $this->post(route('api.products.store'), $data);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        $this->assertTrue(array_key_exists('code', $responseJson));

        $data['code'] = "random";
        $response = $this->post(route('api.products.store'), $data);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(Product::count(), 2);
        $responseJson = $response->json()['data'];
        $this->assertTrue(array_key_exists('code', $responseJson));
    }

    public function test_get() 
    {
        $product = Product::factory()->create();

        $response = $this->get(route('api.products.show', ['product' => $product]));
        $response->assertStatus(Response::HTTP_OK);

        // test with failed data
        $response = $this->get(route('api.products.show', ['product' => -1]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        $this->assertEquals($responseJson['message'], "product_not_found");
    }

    public function test_edit() 
    {
        $product = Product::factory()->create();
        $data = [
            "code" => "test",
            "name" => "test",
            "price" => 12345.0
        ];
        $response = $this->put(route('api.products.update', ['product' => $product]), $data);
        $response->assertStatus(Response::HTTP_OK);

        $product->refresh();
        $this->assertEquals($product->code, $data['code']);
        $this->assertEquals($product->name, $data['name']);
        $this->assertEquals($product->price, $data['price']);

        // should allow even if code is same
        $response = $this->put(route('api.products.update', ['product' => $product]), $data);
        $response->assertStatus(Response::HTTP_OK);

        // should fail if using other code
        $data['code'] = "test2";
        $product2 = Product::create($data);

        $data['code'] = $product->code;
        $response = $this->put(route('api.products.update', ['product' => $product2]), $data);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_delete() 
    {
        $product = Product::factory()->create();
        $response = $this->delete(route('api.products.destroy', ['product' => $product]));
        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals($response->json()['data']['message'], "Product data deleted.");
        $this->assertEquals(Product::count(), 0);

        // test delete same data
        $response = $this->delete(route('api.products.destroy', ['product' => $product]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        
        $this->assertEquals($responseJson['message'], 'product_not_found');
    }
}
