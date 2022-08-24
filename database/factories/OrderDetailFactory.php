<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{

    private static int $counter = 1;

    private static int $order_id = 1;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if(self::$counter === 3){
            self::$counter = 0;
            ++self::$order_id;
        }
        ++self::$counter;
        return [
            'order_id'          => self::$order_id,
            'product_id'        => \rand(1, 10),
            'quantity'          => \rand(1, 10)
        ];
    }
}
