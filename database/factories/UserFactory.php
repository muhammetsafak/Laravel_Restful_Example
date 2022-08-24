<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'          => $this->faker->firstName(),
            'surname'       => $this->faker->lastName(),
            'username'      => $this->faker->userName(),
            'email'         => $this->faker->email(),
            //'password'      => \password_hash($this->faker->password(), \PASSWORD_DEFAULT),
            'password'      => \password_hash('123456', \PASSWORD_DEFAULT),
        ];
    }
}
