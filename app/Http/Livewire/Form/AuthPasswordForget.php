<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AuthPasswordForget extends ModelBase
{
    /**
     * This form is opened by default.
     *
     * @var bool
     */
    public bool $isFormOpen = true;

    /**
     * Decides form can send by key ENTER
     *
     * @var bool
     */
    public bool $canKeyEnterSendForm = true;

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'login'                => 'form::components.form.actions.links.login',
        'email_password_reset' => 'form::components.form.actions.password-forget',
    ];

    /**
     * Overwrite this to setup the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('passwordForget');
    }


    /**
     * @param  mixed  $livewireId
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TelegramSDKException
     * @todo: throttle
     */
    #[On('password-forget')]
    public function passwordForget(mixed $livewireId): void
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        if (!($validatedData = $this->validateForm())) {
            // Errors already set ...
            return;
        }

        $requestedEmail = data_get($this->formObjectAsArray, 'email', '');
        /** @var \Modules\WebsiteBase\app\Models\User $user */
        if ($user = \Modules\WebsiteBase\app\Models\User::getBuilderFrontendItems()
            ->where('email', $requestedEmail)
            ->first()) {

            // Create a new Login Token with at least 10 minutes expiration if non exists
            $minutes = 10;
            $expire = Carbon::now()->addMinutes($minutes)->format(SystemService::dateIsoFormat8601);
            $newExpire = Carbon::now()->addMinutes($minutes + 5)->format(SystemService::dateIsoFormat8601);
            $token = $user->getOrCreateWebsiteToken(minExpire: $expire, newExpire: $newExpire);

            /** @var SendNotificationService $sendNotificationService */
            $sendNotificationService = app(SendNotificationService::class);
            if (!$sendNotificationService->sendNotificationConcern('remember_user_login_data', $user)) {
                $this->addErrorMessages($sendNotificationService->getErrors());
                return;
            }

        } else {
            // no user found, but no error message wanted
            Log::error('User not found: ', [$requestedEmail, __METHOD__]);
        }

        $this->addSuccessMessage(__('maybe_email_sent'));

        // open form again ...
        // $this->openForm(null, true);
        // ... or redirect to login
        $this->redirectRoute('login');
    }

}
