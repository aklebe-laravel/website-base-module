<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;

class Contact extends NativeObjectBase
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
    public bool $canKeyEnterSendForm = false;

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'send' => 'form::components.form.actions.send',
    ];

    /**
     * Overwrite this to setup the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('send');
    }

    /**
     * @param  mixed  $livewireId
     * @return Application|RedirectResponse|Redirector|void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TelegramSDKException
     */
    #[On('send')]
    public function send(mixed $livewireId)
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        if ($validatedData = $this->validateForm()) {
            $this->addSuccessMessage("Message was sent successfully");

            $validatedData['user'] = Auth::user();
            //            Mail::send(new ContactSendMessage($validatedData));
            /** @var SendNotificationService $sendNotificationService */
            $sendNotificationService = app(SendNotificationService::class);
            $sendNotificationService->sendNotificationConcern('contact_request_message', $validatedData['user'],
                ['contactData' => $validatedData]);


            $this->closeForm();
        } else {
            $this->addErrorMessage("Unable to validate message.");
        }
    }
}
