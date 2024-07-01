<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\WebsiteBase\app\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory // \database\factories\UserFactory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'              => fake()->unique()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'shared_id'         => uniqid('js_suid_'),
            'email_verified_at' => now(),
            'password'          => '1234567',
            'remember_token'    => Str::random(10),
            //            'rating'            => fake()->randomFloat(4, 0, 100),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
