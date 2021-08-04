<?php

namespace Database\Factories;

use App\Models\Promo;
use App\Enums\Promo\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Promo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name,
            "code" => $this->faker->word,
            "discount_percentage" => rand(0, 10),
            "minimum_price" => rand(10_000, 100_000),
            "status" => Status::Active,
        ];
    }
}
