<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ShoppingOrder;

class ShoppingOrderFactory extends Factory
{

    protected $model = ShoppingOrder::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'phone_number' => $this->faker->e164phoneNumber,
            'items' => json_encode($this->faker->randomElement(['sugar', 'tea', 'milk', 'beans'])),
            'address' => $this->faker->streetName,
        ];
    }
}
