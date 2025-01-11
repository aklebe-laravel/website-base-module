<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     * Overwrite this to set up the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('send');
    }

    /**
     * @param  mixed  $livewireId
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[On('send')]
    public function send(mixed $livewireId): void
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        if ($validatedData = $this->validateForm()) {

            /** @var SendNotificationService $sendNotificationService */
            $sendNotificationService = app(SendNotificationService::class);

            if ($users = $sendNotificationService->getStaffSupportUsers()) {
                foreach ($users as $user) {
                    $currentUser = Auth::user();
                    Log::info(sprintf("Sending contact message to from '%s' to '%s'", $currentUser->name, $user->name));

                    // To be clear:
                    // 1) $user is the staff support we want to send this message
                    // 2) $validatedData['user'] is the current user/customer who wrote this contact message
                    $validatedData['user'] = $currentUser;
                    $sendNotificationService->sendNotificationConcern('contact_request_message', $user, ['contactData' => $validatedData]);
                }
                $this->addSuccessMessage("Message was sent successfully");
            } else {
                $this->addErrorMessage("Message could not be sent.");
            }

            $this->closeForm();
        } else {
            $this->addErrorMessage("Unable to validate message.");
        }
    }
}
