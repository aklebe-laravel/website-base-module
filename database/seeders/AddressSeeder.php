<?php

namespace Modules\WebsiteBase\database\seeders;

use Illuminate\Support\Facades\Log;
use Modules\SystemBase\database\seeders\BaseModelSeeder;
use Modules\WebsiteBase\app\Models\Address;
use Modules\WebsiteBase\app\Models\User;

class AddressSeeder extends BaseModelSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        parent::run();

        // find seeder start
        if (!($seederStarted = config('seeder_started'))) {
            Log::error("No seeder start found.", [__METHOD__]);
            return;
        }

        // get all new generated users
        $userIds = User::with([])->where('created_at', '>=', $seederStarted)->pluck('id');
        $maxAddressesPerUser = config('seeders.users.addresses.count', 10);
        foreach ($userIds as $userId) {
            // Chance of percent to assign ...
            if (rand(1, 100) < config('seeders.users.addresses.chance_to_add', 0)) {
                // between 1 to x addresses each user ...
                $this->TryCreateFactories(Address::class, rand(1, $maxAddressesPerUser), fn() => ['user_id' => $userId,]);
            }
        }
    }
}
