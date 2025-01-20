<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\WebsiteBase\app\Models\Token;

/**
 * @extends Factory
 */
class TokenFactory extends Factory
{
    protected $model = Token::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purpose' => Token::PURPOSE_LOGIN,
            'token'   => uniqid('l', true),
        ];
    }
}
