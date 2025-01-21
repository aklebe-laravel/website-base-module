<?php

namespace Modules\WebsiteBase\app\Console;

use Illuminate\Console\Command;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\WebsiteBase\app\Services\ConfigService;
use Modules\WebsiteBase\app\Services\CreateChangeLogService;
use Symfony\Component\Console\Command\Command as CommandResult;

class ChangeLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website-base:changelog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate changelog if not already exists. Runs only once. To force it clear cache before.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var CreateChangeLogService $service */
        $service = app(CreateChangeLogService::class);

        $service->updateGitHistories();

        return CommandResult::SUCCESS;
    }
}
