<?php

namespace Modules\WebsiteBase\database\seeders;

use Modules\Acl\app\Models\AclResource;
use Modules\SystemBase\database\seeders\BaseModelSeeder;
use Modules\WebsiteBase\app\Models\Token;
use Modules\WebsiteBase\app\Models\User;

class TokenSeeder extends BaseModelSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        parent::run();

        $users = User::with(['aclGroups.aclResources'])->whereHas('aclGroups.aclResources', function ($query) {
            return $query->where('code', '=', AclResource::RES_NON_HUMAN);
        })->get();

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 8); $i++) {
                $this->TryCreateFactories(Token::class, 1, fn() => [
                    'user_id'    => $user->getKey(),
                    'purpose'    => Token::PURPOSE_LOGIN,
                    'expires_at' => fake()->dateTimeBetween('now', '+4 years'),
                ]);
            }
        }
    }
}
