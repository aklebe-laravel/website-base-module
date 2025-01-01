<?php

namespace Modules\WebsiteBase\app\Console;

use Illuminate\Console\Command;
use Modules\WebsiteBase\app\Services\ConfigService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(): int
    {
        $path = $this->argument('path');
        $storeId = $this->option('store_id') ?: null;
        $module = $this->option('module') ?: null;

        $configService = app(ConfigService::class);
        $v = $configService->get($path, null, $storeId, $module);

        $this->output->writeln(sprintf("Path: '%s', Value: '%s'", $path, $v));
        return CommandResult::SUCCESS;
    }
}
