<?php

namespace Modules\WebsiteBase\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\WebsiteBase\app\Models\Token;

class TokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::with(['aclGroups.aclResources'])->whereHas('aclGroups.aclResources', function ($query) {
            return $query->where('code', '=', 'puppet');
        })->get();

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 8); $i++) {
                Token::factory()
                     ->create([
                         'user_id'    => $user->getKey(),
                         'purpose'    => Token::PURPOSE_LOGIN,
                         'expires_at' => fake()->dateTimeBetween('now', '+4 years'),
                     ]);
            }
        }
    }
}
