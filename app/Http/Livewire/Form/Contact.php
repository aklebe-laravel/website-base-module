<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class Contact extends NativeObjectBase
{
    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Contact Message';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Contact Messages';

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
     * @param  mixed|null  $id
     *
     * @return JsonResource
     */
    public function initDataSource(mixed $id = null): JsonResource
    {
        $object = [
            'content' => '',
        ];

        $this->setDataSource(new JsonResource($object));

        return $this->getDataSource();
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        return [
            ... $parentFormData,
            'title'         => __(''),
            'form_elements' => [
                'content' => [
                    'html_element' => 'textarea',
                    'label'        => __('Content'),
                    'description'  => __('Content of your concern'),
                    'validator'    => [
                        'nullable',
                        'string',
                        'min:3',
                    ],
                    'css_group'    => 'col-12',
                ],
            ],

        ];
    }

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
