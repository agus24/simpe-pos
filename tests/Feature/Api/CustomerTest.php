<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

class CustomerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all()
    {
        $customers = Customer::factory()->count(3)->create();
        $response = $this->get(route('api.customers.index'));
        $response->assertStatus(Response::HTTP_OK);

        $responseJson = $response->json()['data'];

        $this->assertEquals(count($responseJson), 3);
        $this->assertEquals($responseJson[0]['name'], $customers[0]->name);
        $this->assertEquals($responseJson[1]['name'], $customers[1]->name);
        $this->assertEquals($responseJson[2]['name'], $customers[2]->name);
    }

    public function test_create() 
    {
        // test with empty data
        $response = $this->post(route('api.customers.store'), []);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        $this->assertTrue(array_key_exists('name', $responseJson));
        $this->assertTrue(array_key_exists('phone_number', $responseJson));
        $this->assertTrue(array_key_exists('address', $responseJson));

        // success response
        $data = [
            "name" => "test",
            "phone_number" => "12345",
            "address" => "test"
        ];

        $response = $this->post(route('api.customers.store'), $data);
        $response->assertStatus(Response::HTTP_CREATED);
        $responseJson = $response->json()['data'];

        $this->assertEquals(Customer::count(), 1);
        $this->assertEquals($responseJson['name'], $data['name']);
        $this->assertEquals($responseJson['phone_number'], $data['phone_number']);
        $this->assertEquals($responseJson['address'], $data['address']);

        // test duplicate phone number
        $response = $this->post(route('api.customers.store'), $data);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        $this->assertTrue(array_key_exists('phone_number', $responseJson));

        $data['phone_number'] = 1234566;
        $response = $this->post(route('api.customers.store'), $data);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(Customer::count(), 2);
        $responseJson = $response->json()['data'];
        $this->assertTrue(array_key_exists('phone_number', $responseJson));
    }

    public function test_get() 
    {
        $customer = Customer::factory()->create();

        $response = $this->get(route('api.customers.show', ['customer' => $customer]));
        $response->assertStatus(Response::HTTP_OK);

        // test with failed data
        $response = $this->get(route('api.customers.show', ['customer' => -1]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        $this->assertEquals($responseJson['message'], "customer_not_found");
    }

    public function test_edit() 
    {
        $customer = Customer::factory()->create();
        $data = [
            "name" => "test",
            "phone_number" => 12345,
            "address" => "test"
        ];
        $response = $this->put(route('api.customers.update', ['customer' => $customer]), $data);
        $response->assertStatus(Response::HTTP_OK);

        $customer->refresh();
        $this->assertEquals($customer->name, $data['name']);
        $this->assertEquals($customer->phone_number, $data['phone_number']);
        $this->assertEquals($customer->address, $data['address']);

        // should allow even if phone number is same
        $response = $this->put(route('api.customers.update', ['customer' => $customer]), $data);
        $response->assertStatus(Response::HTTP_OK);

        // should fail if using other phone number
        $data['phone_number'] = 12341234;
        $customer2 = Customer::create($data);

        $data['phone_number'] = $customer->phone_number;
        $response = $this->put(route('api.customers.update', ['customer' => $customer2]), $data);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_delete() 
    {
        $customer = Customer::factory()->create();
        $response = $this->delete(route('api.customers.destroy', ['customer' => $customer]));
        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals($response->json()['data']['message'], "Customer data deleted.");
        $this->assertEquals(Customer::count(), 0);

        // test delete same data
        $response = $this->delete(route('api.customers.destroy', ['customer' => $customer]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseJson = $response->json()['data'];
        
        $this->assertEquals($responseJson['message'], 'customer_not_found');
    }
}
