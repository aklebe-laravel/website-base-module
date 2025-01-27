<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class CmsContent extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    protected array $objectRelations = [];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Cms Content';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Cms Content';

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
            'title'        => $this->makeFormTitle($this->getDataSource(), 'code'),
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
                                    'id'            => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'is_enabled'    => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled displaying.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'code'          => [
                                        'html_element' => 'text',
                                        'label'        => __('Code'),
                                        'description'  => 'Code for the content for all locales',
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'parent_id'     => [
                                        'html_element' => 'select',
                                        'label'        => __('Parent'),
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\CmsPage::orderBy('code',
                                            'ASC')->get(), [
                                            'id',
                                            'name',
                                        ], 'id', app('system_base')->selectOptionsSimple[app('system_base')::selectValueNoChoice]),
                                        'description'  => __('Parent Page'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'store_id'      => [
                                        'html_element' => 'select',
                                        'label'        => __('Store'),
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\Store::orderBy('code',
                                            'ASC')->get(), [
                                            'id',
                                            'code',
                                        ], 'id', app('system_base')->selectOptionsSimple[app('system_base')::selectValueNoChoice]),
                                        'description'  => __('The Store assigned to this page'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'format'        => [
                                        'html_element' => 'select',
                                        'label'        => __('Format'),
                                        'options'      => app('system_base')->toHtmlSelectOptions([
                                            'html',
                                            'plain',
                                            'markdown',
                                        ], first: app('system_base')->selectOptionsSimple[app('system_base')::selectValueNoChoice]),
                                        'description'  => __('Format and behaviour of content calculation.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'locale'        => [
                                        'html_element' => 'website-base::select_country',
                                        'label'        => __('Language'),
                                        'description'  => __('Language'),
                                        'validator'    => ['string', 'Max:6'],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'content'       => [
                                        'html_element' => 'textarea',
                                        'options'      => ['rows' => 10],
                                        'label'        => __('Content'),
                                        'description'  => __('Content'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'description'   => [
                                        'html_element' => 'textarea',
                                        // 'options'      => ['rows' => 5],
                                        'label'        => __('Description'),
                                        'description'  => __('Description'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'acl_resources' => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Acl Resources'),
                                        'description'  => __('Permissions'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
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