<?php

namespace Modules\WebsiteBase\app\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Models\User as UserModel;
use Modules\WebsiteBase\app\Services\Notification\Channels\Email;
use Modules\WebsiteBase\app\Services\Notification\Channels\Portal;
use Symfony\Component\Console\Command\Command as CommandResult;

class User extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website-base:user {--user_ids=} {--repair}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Can repair user settings like missing preferred notification channel. Without option --repair the specific users will just displayed.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $idsStr = trim($this->option('user_ids', ''));
        $ids = $idsStr ? explode(',', $this->option('user_ids', '')) : [];
        $repair = $this->option('repair') ?: false;

        $users = app(UserModel::class)->with([]);
        if ($ids) {
            $users->whereIn('id', $ids);
        }

        $usersProcessIndex = 0;
        $this->output->writeln("Processing users: {$users->count()}");
        $users->chunk(100, function ($users) use ($repair, &$usersProcessIndex) {
            /** @var UserModel $user */
            foreach ($users as $user) {
                if ($user->hasAclResource(AclResource::RES_PUPPET)) {
                    //$this->output->writeln(sprintf("user: %8s is not a human", $user->getKey()));
                } else {
                    if ($channel = $user->calculatedNotificationChannel()) {
                        //$this->output->writeln(sprintf("user: %8s : %s : %s", $user->getKey(), $user->name, $channel));
                    } else {
                        $newChannel = null;
                        if (true) { // @todo: some restrictions?
                            if ($user->canUseEmail()) {
                                $newChannel = Email::name;
                            } else {
                                $newChannel = Portal::name;
                            }
                        }

                        if ($newChannel) {
                            if ($repair) {
                                $user->setExtraAttribute(ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS, [$newChannel]);
                                if ($user->save()) {
                                    $msg = sprintf("user: %8s : %s : 'NEW CHANNEL' : %s", $user->getKey(), $user->name, $newChannel);
                                    $this->output->writeln($msg);
                                    Log::info($msg);
                                }
                            } else {
                                $msg = sprintf("user: %8s : %s : has no channel and would become : %s", $user->getKey(), $user->name, $newChannel);
                                $this->output->writeln($msg);
                            }
                            $usersProcessIndex++;
                        }
                    }

                }
            }
        });

        $this->output->writeln("Processed users: $usersProcessIndex");
        return CommandResult::SUCCESS;
    }
}
