<?php

namespace Modules\WebsiteBase\app\Forms;

use Illuminate\Support\Facades\Auth;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\WebsiteBase\app\Forms\Base\ModelBaseExtraAttributes;

class UserProfile extends ModelBaseExtraAttributes
{
    /**
     * Relation method if parent form exists.
     */
    const string PARENT_RELATION_METHOD_NAME = 'users';

    /**
     * Needed in Userprofile because the Classname differ
     *
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
    protected array $objectRelations = [
        'mediaItems',
        'avatars',
        'aclGroups',
    ];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'User Profile';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'User Profiles';

    /**
     * @param  bool  $canAuthId  *
     *
     * @return mixed
     */
    public function getOwnerUserId(bool $canAuthId = true): mixed
    {
        return $this->getDataSource()->getKey();
    }

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

        $defaultSettings = $this->getDefaultFormSettingsByPermission();

        if ($defaultSettings['can_edit']) {
            $extraAttributeTab = $this->getTabExtraAttributes($this->getDataSource());
        } else {
            $extraAttributeTab = [];
        }

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->getDataSource(), 'name'),
            'tab_controls' => [
                'base_item' => [
                    'disabled'  => $defaultSettings['disabled'],
                    'tab_pages' => [
                        'common'        => [
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
                                    'shared_id'            => [
                                        'html_element' => 'hidden',
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                    ],
                                    'name'                 => [
                                        'html_element' => $defaultSettings['can_edit'] ? 'text' : 'label',
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
                                        'html_element' => $defaultSettings['can_edit'] ? 'email' : 'label',
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
                                        'visible'       => $defaultSettings['can_edit'],
                                        'auto_complete' => false,
                                        'html_element'  => 'password',
                                        'label'         => __('Password'),
                                        'description'   => __('leave_password_blank'),
                                        'validator'     => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'     => 'col-12 col-md-6',
                                    ],
                                    '__confirm__password'  => [
                                        'visible'       => $defaultSettings['can_edit'],
                                        'auto_complete' => false,
                                        'html_element'  => 'password',
                                        'label'         => __('Confirm Password'),
                                        'description'   => __('leave_password_blank'),
                                        'validator'     => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'     => 'col-12 col-md-6',
                                    ],
                                    'imageMaker.final_url' => [
                                        'html_element' => 'image',
                                        'label'        => __('Image'),
                                        'description'  => __('Image'),
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'media_file_upload'    => [
                                        'visible'      => $defaultSettings['can_edit'],
                                        'html_element' => 'website-base::media_item_file_upload_images',
                                        'label'        => __('Media Upload'),
                                        'description'  => __('media_user_upload_description'),
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                ],
                            ],
                        ],
                        'avatars'       => [
                            'visible'  => $defaultSettings['can_edit'] && $this->formLivewire->viewModeAtLeast(),
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Avatars'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'avatars' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Avatars'),
                                        'description'  => __('Avatars by this user'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.media-item',
                                            'table'         => 'website-base::data-table.media-item-image-user-avatar',
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
                        'addresses'     => [
                            'visible'  => $defaultSettings['can_edit'] && $this->formLivewire->viewModeAtLeast(),
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
                                                'hasCommands' => $defaultSettings['can_edit'],
                                                'editable'    => $defaultSettings['can_edit'],
                                                'canAddRow'   => $defaultSettings['can_edit'],
                                                'removable'   => $defaultSettings['can_edit'],
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
                        'images'        => [
                            'visible'  => app('website_base_config')->getValue('users.profiles.media.enabled', false) && $this->formLivewire->viewModeAtLeast(NativeObjectBase::viewModeExtended),
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Images'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'images' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Images'),
                                        'description'  => __('Images assigned to this product'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.media-item',
                                            'table'         => 'website-base::data-table.media-item-image-user-avatar',
                                            'table_options' => [
                                                'hasCommands' => $defaultSettings['can_edit'],
                                                'editable'    => $defaultSettings['can_edit'],
                                                'canAddRow'   => $defaultSettings['can_edit'],
                                                'removable'   => $defaultSettings['can_edit'],
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
                        'acl_groups'    => [
                            'visible'  => ($defaultSettings['can_manage'] || ($defaultSettings['can_edit'] && $this->getDataSource()->aclGroups->count() > 0)) && $this->formLivewire->viewModeAtLeast(NativeObjectBase::viewModeExtended),
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Acl Groups'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'aclGroups' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Acl Groups'),
                                        'description'  => __('Acl Groups'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'acl::form.acl-group',
                                            'table'         => 'acl::data-table.acl-group',
                                            'table_options' => [],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'acl_resources' => [
                            'visible'  => ($defaultSettings['can_manage'] || ($defaultSettings['can_edit'] && $this->getDataSource()->aclResources->count() > 0)) && $this->formLivewire->viewModeAtLeast(),
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
                                            'form'          => 'acl::form.acl-resource',
                                            'table'         => 'acl::data-table.acl-resource',
                                            'table_options' => [
                                                'description'         => __('All acl resources found for all groups by this user.'),
                                                'filterByParentOwner' => false,
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
                        'tokens'        => [
                            'visible'  => $defaultSettings['can_edit'] && $this->formLivewire->viewModeAtLeast(NativeObjectBase::viewModeExtended),
                            'disabled' => !$this->getDataSource()->getKey(),
                            'tab'      => [
                                'label' => __('Tokens'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'tokens' => [
                                        'html_element' => 'element-dt-selected-no-interaction',
                                        'label'        => __('Tokens'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.token',
                                            'table'         => 'website-base::data-table.token',
                                            'table_options' => [],
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