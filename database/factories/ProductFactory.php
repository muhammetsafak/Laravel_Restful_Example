<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->word(),
            'price'     => $this->faker->randomFloat(null, 1, 100),
            'stock'     => \rand(100, 1000),
        ];
    }
}
