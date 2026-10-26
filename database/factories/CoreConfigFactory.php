<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\WebsiteBase\app\Models\CoreConfig;

class CoreConfigFactory extends Factory
{
    protected $model = CoreConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path'        => implode('.', fake()->words(rand(1, 4))),
            'label'       => fake()->unique()->words(rand(1, 3), true),
            'description' => fake()->unique()->words(rand(5, 15), true),
            'value'       => implode(' ', fake()->words(rand(3, 4))),
        ];
    }
}
