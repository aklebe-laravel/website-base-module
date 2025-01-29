<?php

namespace Modules\WebsiteBase\app\Forms;

use Illuminate\Support\Facades\Auth;
use Modules\WebsiteBase\app\Forms\Base\ModelBaseExtraAttributes;

class User extends ModelBaseExtraAttributes
{
    /**
     * Relation method if parent form exists.
     */
    const string PARENT_RELATION_METHOD_NAME = 'users';

    /**
     * Set for example 'web_uri' or 'shared_id' to try load from this if is not numeric in initDataSource().
     * Model have to be trait by TraitBaseModel to become loadByFrontEnd()
     *
     * @var string
     */
    public const string frontendKey = 'shared_id';

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
    protected array $objectRelations = [
        'mediaItems',
        'aclGroups',
    ];

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

    public function getOwnerUserId(): mixed
    {
        return $this->getDataSource()->getKey();
    }

    public function isOwnUser(): bool
    {
        return $this->getDataSource() && ($this->getOwnerUserId() == Auth::id());
    }

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled' => false,
            'is_deleted' => false,
            'shared_id'  => uniqid('js_suid_'),
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

        $extraAttributeTab = $this->getTabExtraAttributes($this->getDataSource());

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->getDataSource(), 'name'),
            'tab_controls' => [
                'base_item' => [
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'                   => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'name'                 => [
                                        'html_element' => 'text',
                                        'label'        => __('Username'),
                                        'description'  => __('Username'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'email'                => [
                                        'html_element' => 'email',
                                        'label'        => __('Email'),
                                        'description'  => __('User Email'),
                                        'validator'    => [
                                            'required',
                                            'email',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'password'             => [
                                        'html_element' => 'password',
                                        'label'        => __('Password'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    '__confirm__password'  => [
                                        'html_element' => 'password',
                                        'label'        => __('Confirm Password'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'shared_id'            => [
                                        'html_element' => 'label_and_hidden',
                                        'label'        => __('Shared ID'),
                                        'description'  => __('Shared ID'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'imageMaker.final_url' => [
                                        'html_element' => 'image',
                                        'label'        => __('Image'),
                                        'description'  => __('Image'),
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'media_file_upload'    => [
                                        'html_element' => 'website-base::media_item_file_upload_images',
                                        'label'        => __('Media Upload'),
                                        'description'  => __('media_user_upload_description'),
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'visible'  => $defaultSettings['can_edit'],
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Addresses'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'addresses' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Addresses'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.address',
                                            'form_options'  => [],
                                            'table'         => 'website-base::data-table.address',
                                            'table_options' => [
                                                'hasCommands' => $defaultSettings['can_manage'],
                                                'editable'    => $defaultSettings['can_manage'],
                                                'canAddRow'   => $defaultSettings['can_manage'],
                                                'removable'   => $defaultSettings['can_manage'],
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Images'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'mediaItems' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Images'),
                                        'description'  => __('Images assigned to this product'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.media-item',
                                            'table'         => 'website-base::data-table.media-item-image-user-avatar',
                                            'table_options' => [
                                                'hasCommands' => $defaultSettings['can_manage'],
                                                'editable'    => $defaultSettings['can_manage'],
                                                'canAddRow'   => $defaultSettings['can_manage'],
                                                'removable'   => $defaultSettings['can_manage'],
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Acl Groups'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'aclGroups' => [
                                        'html_element' => 'element-dt-split-default',
                                        'label'        => __('Acl Groups'),
                                        'description'  => __('Acl Groups'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'table'         => 'acl::data-table.acl-group',
                                            'table_options' => [
                                                'hasCommands' => $defaultSettings['can_manage'],
                                                'editable'    => $defaultSettings['can_manage'],
                                                'canAddRow'   => $defaultSettings['can_manage'],
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Acl Resources'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'aclResources' => [
                                        'html_element' => 'element-dt-selected-no-interaction',
                                        'label'        => __('Acl Resources'),
                                        'description'  => __('Acl Resources'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'table' => 'acl::data-table.acl-resource',
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Tokens'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'tokens' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Tokens'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.token',
                                            'table'         => 'website-base::data-table.token',
                                            'table_options' => [
                                                'hasCommands' => $defaultSettings['can_manage'],
                                                'editable'    => $defaultSettings['can_manage'],
                                                'canAddRow'   => $defaultSettings['can_manage'],
                                                'removable'   => $defaultSettings['can_manage'],
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        $extraAttributeTab,
                    ],
                ],
            ],
        ];
    }

}