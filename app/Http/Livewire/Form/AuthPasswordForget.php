<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Http\Livewire\Form\Base\AuthBase;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class AuthPasswordForget extends AuthBase
{
    /**
     * @var string|null
     */
    protected ?string $objectEloquentModelName = \Modules\WebsiteBase\app\Models\User::class;

    /**
     * Relations for using in with().
     * Don't add fake relations or relations should not be updated!
     *
     * Will be used as:
     * - Blacklist of properties, to save the plain model
     * - onAfterUpdateItem() to sync() the relations
     *
     * @var array[]
     */
    public array $objectRelations = [];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'User';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Users';

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'login'                => 'form::components.form.actions.links.login',
        'email_password_reset' => 'form::components.form.actions.password-forget',
    ];

    /**
     * Overwrite this to set up the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('passwordForget');
    }


    /**
     * @param  mixed  $livewireId
     *
     * @return void
     */
    #[On('password-forget')]
    public function passwordForget(mixed $livewireId): void
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        if (!$this->ensureIsNotRateLimited()) {
            $this->addErrorMessage(__('Too many tries.'));
            Log::error("Send user password failed. Rate Limiter.", [__METHOD__]);

            return;
        }

        if (!($validatedData = $this->validateForm())) {
            // (Errors already set)
            // Open this form again (with errors)!
            $this->reopenFormIfNeeded();

            return;
        }

        $requestedEmail = data_get($this->dataTransfer, 'email', '');
        /** @var \Modules\WebsiteBase\app\Models\User $user */
        $user = \Modules\WebsiteBase\app\Models\User::getBuilderFrontendItems()->where('email', $requestedEmail)->orWhere('name', $requestedEmail)->first();
        if (!$user || !$user->canLogin()) {
            RateLimiter::hit($this->throttleKey());
            // no user found, but no error message wanted
            Log::error('User not found: ', [$requestedEmail, __METHOD__]);

            return;
        }

        // Create a new Login Token with at least 10 minutes expiration if non exists
        $minutes = 10;
        $expire = Carbon::now()->addMinutes($minutes)->format(SystemService::dateIsoFormat8601);
        $newExpire = Carbon::now()->addMinutes($minutes + 5)->format(SystemService::dateIsoFormat8601);
        $token = $user->getOrCreateWebsiteToken(minExpire: $expire, newExpire: $newExpire);

        /** @var SendNotificationService $sendNotificationService */
        $sendNotificationService = app(SendNotificationService::class);
        if (!$sendNotificationService->sendNotificationConcern('remember_user_login_data', $user)) {
            $this->addErrorMessages($sendNotificationService->getErrors());
            // Open this form again (with errors)!
            $this->reopenFormIfNeeded();

            return;
        }

        // use session this time because we redirect to another route
        $this->addSuccessMessage(__('maybe_email_sent'), true);

        // redirect to login page
        $this->redirectRoute('login');
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        // Remove "special" description for empty objects!
        $parentFormData['description'] = '';

        return [
            ... $parentFormData,
            'title'         => __('Forgot your password?'),
            'form_elements' => [
                'email' => [
                    'html_element' => 'email',
                    'id'           => 'email',
                    'label'        => __('EmailOrUsername'),
                    'validator'    => ['required', 'string', 'max:255'],
                    'css_group'    => 'col-12',
                ],
            ],
        ];
    }
}
