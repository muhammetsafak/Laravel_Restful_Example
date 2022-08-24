<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    private static int $counter = 1;

    private static int $user_id = 1;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if(self::$counter === 5){
            ++self::$user_id;
            self::$counter = 0;
        }
        ++self::$counter;
        return [
            'user_id'       => self::$user_id,
            'address'       => $this->faker->address(),
            'price'         => $this->faker->randomFloat(null, 1, 100),
            'status'        => 'waiting'
        ];
    }
}
