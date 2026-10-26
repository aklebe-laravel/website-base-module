<?php

namespace Modules\WebsiteBase\database\seeders;

use Modules\SystemBase\database\seeders\BaseModelSeeder;
use Modules\WebsiteBase\app\Models\Store;
use Modules\WebsiteBase\app\Models\User;

class StoreSeeder extends BaseModelSeeder
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
            return $query->where('code', '=', 'admin');
        })->get();
        /** @var User $user */
        $user = $users->first();

        $this->TryCreateFactories(Store::class, 20, fn() => ['user_id' => $user->id ?? null]);
    }
}
