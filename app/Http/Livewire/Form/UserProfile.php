<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\On;
use Modules\WebsiteBase\app\Models\User as UserModel;
use Modules\WebsiteBase\app\Services\WebsiteService;
use Modules\WebsiteBase\app\Services\WebsiteTelegramService;

class UserProfile extends User
{
    /**
     * @var WebsiteTelegramService
     */
    protected WebsiteTelegramService $websiteTelegramService;

    /**
     * Override to adjust listeners, wich is working well.
     * Use mount instead of __construct to inject services.
     *
     * @param $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->websiteTelegramService = app(WebsiteTelegramService::class);
    }

    /**
     * @param  mixed  $requestUser
     */
    #[On('telegram-assign-me')]
    public function telegramAssignMe(mixed $requestUser): void
    {
        if (!Auth::check() || !($user = Auth::user())) {
            $this->addErrorMessage('No user logged in. Session expired?');
            return;
        }

        $telegramIdentityModelData = [
            'telegram_id'  => data_get($requestUser, 'id'),
            'display_name' => data_get($requestUser, 'first_name'),
            'username'     => data_get($requestUser, 'username'),
            // 'img' => data_get($requestUser, 'photo_url'),
        ];

        // Check telegram id already in used ...
        if ($foundUserId = $this->websiteTelegramService->findUserByTelegramId($telegramIdentityModelData['telegram_id'])) {

            // The found telegram id is not owned by the current user ...
            if ($foundUserId != $user->getKey()) {
                $this->addErrorMessage("Es gibt bereits einen Benutzer, der diesem Telegram-Account zugewiesen wurde.");
                return;
            }

        }

        // Create Telegram Identity only, not the user model!
        if ($telegramIdentityFound = $this->websiteTelegramService->ensureTelegramIdentity($telegramIdentityModelData)) {

            // add extra attribute: telegram_id
            // @todo: move this to event to prevent WebsiteBase stuff from this module
            $user->setExtraAttribute('telegram_id', $telegramIdentityFound->telegram_id);
            $user->setExtraAttribute('use_telegram', true);
            $user->setExtraAttribute('preferred_notification_channel', WebsiteService::NOTIFICATION_CHANNEL_TELEGRAM);
            // save it
            if ($user->save()) {
                $this->addSuccessMessage(sprintf("Die Telegram-Verknüpfung für '%s' wurde erfolgreich angelegt ",
                    data_get($requestUser, 'first_name')));
                // reopen form to reload new user data
                $this->openForm($user->getKey());
                return;
            }

        }

        $this->addErrorMessage('Die Telegram-Verknüpfung konnte leider nicht angelegt werden.');
        // // reopen form to reset telegram widget - not working
        // $this->openForm($user->getKey());
    }

    /**
     * @param  mixed  $livewireId
     * @param  mixed  $userId
     */
    #[On('telegram-delete-me')]
    public function telegramDeleteMe(mixed $livewireId, mixed $userId): void
    {
        if (!$this->checkLivewireId($livewireId)) {
            $this->addErrorMessage(__('Wrong Livewire ID.'));
            return;
        }

        /** @var UserModel $user */
        if (!($user = app(UserModel::class)->with([])->whereId($userId)->first())) {
            $this->addErrorMessage(__('User not found.'));
            return;
        }

        // dont delete if it's a fake email
        if ($user->hasFakeEmail()) {
            $this->addErrorMessage(__('unable_to_delete_users_last_identity'));
            return;
        }

        // remove extra attribute: telegram_id
        $user->setExtraAttribute('telegram_id', null);
        $user->setExtraAttribute('use_telegram', false);
        // remove preferred channel when its telegram
        if (data_get($user->extraAttributes,
                'preferred_notification_channel') === WebsiteService::NOTIFICATION_CHANNEL_TELEGRAM) {
            $user->setExtraAttribute('preferred_notification_channel', null);
        }
        // save it
        if ($user->save()) {
            $this->addSuccessMessage("Die Telegram-Verknüpfung wurde erfolgreich entfernt ");
            // reopen form to reload new user data
            $this->openForm($user->getKey());
        }
    }

    /**
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     *
     * @return bool
     * @throws \Exception
     */
    #[On('delete-item')]
    public function deleteItem(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var \Modules\WebsiteBase\app\Models\User $user */
        if ($user = app(\App\Models\User::class)->with([])->find($itemId)) {
            $result = $user->deleteIn3Steps();
            if ($result['success']) {
                $this->addSuccessMessage(__("User was deleted."));
            } else {
                $this->addErrorMessages($result['message']);
            }
        }

        Redirect::route('login')->with('message', 'Operation Successful!'); // maybe message never shown this way
        return true;
    }


}
