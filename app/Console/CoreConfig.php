<?php

namespace Modules\WebsiteBase\app\Console;

use Illuminate\Console\Command;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\WebsiteBase\app\Services\ConfigService;
use Symfony\Component\Console\Command\Command as CommandResult;

class CoreConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website-base:core-config {path} {--store_id=} {--module=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'read a core config value';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $path = $this->argument('path');
        $storeId = $this->option('store_id') ?: null;
        $storeId = (trim(strtolower($storeId)) === "null") ? null : $storeId;
        $module = $this->option('module') ?: null;
        $module = (trim(strtolower($module)) === "null") ? null : $module;
        if ($module) {
            $module = ModuleService::getSnakeName($module);
        }

        $configService = app(ConfigService::class);
        $v = $configService->getValue($path, null, $storeId, $module);

        $this->output->writeln(sprintf("Path: '%s', Value: '%s'", $path, $v));
        return CommandResult::SUCCESS;
    }
}
