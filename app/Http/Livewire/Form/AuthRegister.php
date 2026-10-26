<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Modules\WebsiteBase\app\Http\Livewire\Form\Base\AuthBase;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Models\User as UserModel;
use Modules\WebsiteBase\app\Services\Notification\Channels\Email;

class AuthRegister extends AuthBase
{
    /**
     * @var string|null
     */
    public ?string $objectEloquentModelName = UserModel::class;

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
    public string $objectFrontendLabel = 'User';

    /**
     * Plural
     *
     * @var string
     */
    public string $objectsFrontendLabel = 'Users';

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'already-registered' => 'form::components.form.actions.links.already-registered',
        'register'           => 'form::components.form.actions.register',
    ];

    /**
     * Overwrite this to set up the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('register');
    }

    /**
     * @param  mixed  $livewireId
     *
     * @return RedirectResponse|void
     */
    #[On('register')]
    public function register(mixed $livewireId)
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        $res = $this->saveFormData();

        if (!$res->hasErrors()) {

            if ($userId = data_get($res->responseData, 'data.created.0')) {

                /** @var UserModel $user */
                $user = app('user')
                    ->with([])
                    ->find($userId);

                // default preferred channel: email
                $user->setExtraAttribute(ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS, [Email::name]);
                $user->save();

                // @todo: Create new Registered listener to send email

                // @todo: Email to SiteOwner or stuff?

                event(new Registered($user));

                Auth::login($user);

                return redirect()->intended();

            } else {
                // fail
            }

        } else {
            $this->addErrorMessages($res->getErrors());
            // Open this form again (with errors)!
            $this->reopenFormIfNeeded();
        }

    }

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), [
            'shared_id' => uniqid('js_suid_'),
        ]);
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
            'title'         => __('Register'),
            'form_elements' => [
                'shared_id'                           => [
                    'html_element' => 'hidden',
                    'validator'    => [
                        'nullable',
                        'string',
                        'Max:255',
                    ],
                ],
                'name'                                => [
                    'html_element' => 'text',
                    'id'           => 'name',
                    'label'        => __('Username'),
                    'validator'    => [
                        'required',
                        'string',
                        'max:255',
                        'unique:users',
                    ],
                    'css_group'    => 'form-group-responsive-full',
                ],
                'email'                               => [
                    'html_element' => 'email',
                    'label'        => __('Email'),
                    'validator'    => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                        'unique:users',
                    ],
                    'css_group'    => 'form-group-responsive-full',
                ],
                'password'                            => [
                    'html_element' => 'password',
                    'label'        => __('Password'),
                    'validator'    => [
                        'required',
                        'string',
                        'min:3',
                    ],
                    'css_group'    => 'form-group-responsive-full',
                ],
                '__confirm__password'                 => [
                    'html_element' => 'password',
                    'label'        => __('Confirm Password'),
                    'validator'    => [
                        'nullable',
                        'string',
                        'Max:255',
                    ],
                    'css_group'    => 'form-group-responsive-full',
                ],
                'extra_attributes.user_register_hint' => [
                    'html_element' => 'textarea',
                    'label'        => __('User Register Hint'),
                    'description'  => __('User Register Hint'),
                    'validator'    => [
                        'nullable',
                        'string',
                        'Max:255',
                    ],
                    'css_group'    => 'form-group-responsive-full',
                ],
            ],
        ];
    }

}
