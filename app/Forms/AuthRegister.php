<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\WebsiteBase\app\Forms\Base\ModelBaseExtraAttributes;

class AuthRegister extends ModelBaseExtraAttributes
{
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
    protected array $objectRelations = [];

    /**
     * Singular
     * @var string
     */
    protected string $objectFrontendLabel = 'User';

    /**
     * Plural
     * @var string
     */
    protected string $objectsFrontendLabel = 'Users';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
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
                        'Max:255'
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
                        'unique:users'
                    ],
                    'css_group'    => 'col-12',
                ],
                'email'                               => [
                    'html_element' => 'email',
                    'label'        => __('Email'),
                    'validator'    => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                        'unique:users'
                    ],
                    'css_group'    => 'col-12',
                ],
                'password'                            => [
                    'html_element' => 'password',
                    'label'        => __('Password'),
                    'validator'    => [
                        'required',
                        'string',
                        'min:3',
                    ],
                    'css_group'    => 'col-12',
                ],
                '__confirm__password'                 => [
                    'html_element' => 'password',
                    'label'        => __('Confirm Password'),
                    'validator'    => [
                        'nullable',
                        'string',
                        'Max:255'
                    ],
                    'css_group'    => 'col-12',
                ],
                'extra_attributes.user_register_hint' => [
                    'html_element' => 'textarea',
                    'label'        => __('User Register Hint'),
                    'description'  => __('User Register Hint'),
                    'validator'    => [
                        'nullable',
                        'string',
                        'Max:255'
                    ],
                    'css_group'    => 'col-12',
                ],
            ],
        ];
    }

}