<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Support\Facades\Auth;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\WebsiteBase\app\Models\Token as TokenModel;

class Token extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    public array $objectRelations = [];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Token';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Tokens';

    /**
     * @return bool
     */
    public function isOwnUser(): bool
    {
        return $this->getDataSource() && ($this->getOwnerUserId() == Auth::id());
    }

    /**
     * Should be overwritten to decide the current object is owned by user
     * canEdit() can call canManage() but don't call canEdit() in canManage()!
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return ($this->isOwnUser() || $this->canManage());
    }

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), [
            'purpose' => TokenModel::PURPOSE_LOGIN,
            'token'   => uniqid('tkf-', true),
            'user_id' => $this->getOwnerUserId(),
        ]);
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        $defaultSettings = $this->getDefaultFormSettingsByPermission();

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->getDataSource(), 'id'),
            'tab_controls' => [
                'base_item' => [
                    'disabled'  => $defaultSettings['disabled'],
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'          => [
                                        'html_element' => 'hidden',
                                        'label'        => 'ID',
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'user_id'     => [
                                        'html_element' => 'hidden',
                                        'validator'    => [
                                            'required',
                                            'integer',
                                        ],
                                    ],
                                    'purpose'     => [
                                        'html_element' => 'select',
                                        'options'      => app('system_base')->toHtmlSelectOptions(TokenModel::PURPOSE_LIST),
                                        'label'        => __('Purpose'),
                                        'description'  => __('Purpose'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-8',
                                    ],
                                    'user.avatar' => [
                                        'html_element' => 'image',
                                        'label'        => __('Owner'),
                                        'description'  => __('Owner'),
                                        'css_group'    => 'col-12 col-md-4',
                                    ],
                                    'token'       => [
                                        'html_element' => 'text',
                                        'label'        => __('Token'),
                                        'description'  => __('Token'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-lg-8',
                                    ],
                                    'expires_at'  => [
                                        'html_element' => 'datetime-local',
                                        'label'        => __('Expires At'),
                                        'description'  => __('When token becomes invalid.'),
                                        'validator'    => ['nullable', 'date'],
                                        'css_group'    => 'col-12 col-lg-4',
                                    ],
                                    'values'      => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Values'),
                                        'description'  => __('Taken values depends on purpose'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'description' => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Description'),
                                        'description'  => __('Description'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

}
