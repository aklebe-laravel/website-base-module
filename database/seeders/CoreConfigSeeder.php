<?php

namespace Modules\WebsiteBase\database\seeders;

use Modules\SystemBase\database\seeders\BaseModelSeeder;
use Modules\WebsiteBase\app\Models\CoreConfig;

class CoreConfigSeeder extends BaseModelSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        parent::run();

        $this->TryCreateFactories(CoreConfig::class, config('seeders.core_config.count', 10));
    }
}
