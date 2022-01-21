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
            'order_no' => $this->faker->unique()->domainWord,
            'customer_id' => $this->faker->randomElement([1, 2, 3, 4]),
            'items' => json_encode($this->faker->randomElement(['sugar', 'tea', 'milk', 'beans'])),
            'amount' => $this->faker->numberBetween($min = 10000, $max = 95000),
            'address' => $this->faker->streetName,

        ];
    }
}
