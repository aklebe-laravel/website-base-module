<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\WebsiteBase\app\Forms\Base\ModelBaseExtraAttributes;

class AuthLogin extends ModelBaseExtraAttributes
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
            'title'         => __('Login'),
            'form_elements' => [
                'email'    => [
                    'html_element' => 'email',
                    'id'           => 'email',
                    'label'        => __('Email'),
                    'validator'    => ['required', 'string', 'email', 'max:255'],
                    'css_group'    => 'col-12',
                ],
                'password' => [
                    'html_element' => 'password',
                    'label'        => __('Password'),
                    'validator'    => ['required', 'string', 'min:3',],
                    'css_group'    => 'col-12',
                ],
            ],
        ];
    }

}