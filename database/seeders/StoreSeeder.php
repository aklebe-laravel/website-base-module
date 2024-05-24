<?php

namespace Modules\WebsiteBase\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\WebsiteBase\app\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::with(['aclGroups.aclResources'])->whereHas('aclGroups.aclResources', function ($query) {
            return $query->where('code', '=', 'admin');
        })->get();
        /** @var User $user */
        $user = $users->first();
        Store::factory()
             ->count(20)
             ->create(['user_id' => $user->id ?? null]);
    }
}
