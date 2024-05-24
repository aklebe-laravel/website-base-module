<?php

namespace Modules\WebsiteBase\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\WebsiteBase\app\Models\Address;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $users = User::with(['aclGroups'])->whereHas('aclGroups', function ($query) {
            return $query->where('name', '=', 'Traders');
        })->get();

        foreach ($users as $user) {
            // Chance of 75% to assign ...
            if (rand(1, 100) < 75) {
                // between 1 to 10 addresses each user ...
                for ($i = 0; $i < rand(1, 10); $i++) {
                    Address::factory()
                           ->create([
                               'user_id' => $user->id,
                           ]);
                }
            }
        }
    }
}
