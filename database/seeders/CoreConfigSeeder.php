<?php

namespace Modules\WebsiteBase\database\seeders;

use Illuminate\Database\Seeder;
use Modules\WebsiteBase\app\Models\CoreConfig;

class CoreConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CoreConfig::factory()
                  ->count(20)
                  ->create();

    }
}
