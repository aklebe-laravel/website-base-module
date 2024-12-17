<?php

namespace Modules\WebsiteBase\app\Console;

use Illuminate\Console\Command;
use Modules\WebsiteBase\app\Services\WebsiteService;
use Symfony\Component\Console\Command\Command as CommandResult;

class AttributeCleanups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website-base:attr-clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all attribute assignments where model no longer exists.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var WebsiteService $websiteService */
        $websiteService = app(WebsiteService::class);
        $websiteService->cleanupExtraAttributes();

        return CommandResult::SUCCESS;
    }

}
