<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;

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
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled' => 0,
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
                                        'description' => __('The Store assigned to this Content'),
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