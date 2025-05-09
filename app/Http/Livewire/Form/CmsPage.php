<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;

class CmsPage extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * Also used for:
     * - blacklist for properties to clean up the object if needed
     * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    public array $objectRelations = [];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Cms Page';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Cms Pages';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled' => 0,
            'locale'     => config('app.locale', 'en'),
            'store_id'   => app('website_base_settings')->getStoreId(),
        ]);
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        /** @var FormService $formService */
        $formService = app(FormService::class);
        /** @var SystemService $systemService */
        $systemService = app('system_base');

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
                                    'name'          => [
                                        'html_element' => 'text',
                                        'label'        => __('Name'),
                                        'description'  => 'Label for Navigations',
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'web_uri'       => [
                                        'html_element' => 'text',
                                        'label'        => __('Route'),
                                        'description'  => 'Route part after "cms/" of this page',
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'title'         => [
                                        'html_element' => 'text',
                                        'label'        => __('title'),
                                        'description'  => 'Title of this page',
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'parent_id'     => [
                                        'html_element' => 'select',
                                        'label'        => __('Parent'),
                                        'options'      => $systemService->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\CmsPage::orderBy('code',
                                            'ASC')->get(),
                                            [
                                                'id',
                                                'name',
                                            ],
                                            'id',
                                            $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]),
                                        'description'  => __('Parent Page'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'store_id'      => $formService->getFormElement(ExtraAttributeModel::ATTR_STORE, [
                                        'description' => __('The Store assigned to this page'),
                                        'css_group'   => 'col-12 col-lg-6',
                                    ]),
                                    'format'        => [
                                        'html_element' => 'select',
                                        'label'        => __('Format'),
                                        'options'      => $systemService->toHtmlSelectOptions([
                                            'html',
                                            'plain',
                                            'markdown',
                                        ], first: $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]),
                                        'description'  => __('Format and behaviour of content calculation.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'locale'        => $formService->getFormElement(ExtraAttributeModel::ATTR_COUNTRY, [
                                        'label'       => __('Language'),
                                        'description' => __('Language'),
                                    ]),
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
                                    'meta_data'     => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Meta Data'),
                                        'description'  => __('Meta Data used for searching'),
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
