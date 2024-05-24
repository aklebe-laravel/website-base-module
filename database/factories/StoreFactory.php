<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            //            'parent_id' => fake()->boolean(80) ? fake()->randomNumber(2) : null,
            //            'user_id'     => User::with([])->get()->first()->id,
            'code'   => 'Store ' . implode(' ', fake()->unique()->words(rand(1, 2))),
            'url'    => 'https://' . fake()->word() . '.local.test',
//            'rating' => fake()->randomFloat(4, 0, 100),
        ];
    }
}
