<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\WebsiteBase\app\Models\Store;

/**
 * @extends Factory
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code'   => fake()->unique()->words(rand(1, 3), true),
            'url'    => 'https://' . fake()->word() . '.local.test',
        ];
    }
}
