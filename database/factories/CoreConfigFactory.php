<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CoreConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            //            'store_id'     => Store::with([])->get()->first()->id,
            'path'  => implode('.', fake()->words(rand(1, 4))),
            'value' => implode(' ', fake()->words(rand(3, 4))),
        ];
    }
}
