<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class ViewTemplate extends ModelBase
{
    /**
     *
     * @var array[]
     */
    protected array $objectRelations = [];

    /**
     * @var string
     */
    protected string $objectFrontendLabel = 'View Template';

    /**
     * @var string
     */
    protected string $objectsFrontendLabel = 'View Templates';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled'        => true,
            'parameter_variant' => \Modules\WebsiteBase\app\Models\ViewTemplate::PARAMETER_VARIANT_DEFAULT,
        ]);
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->jsonResource, 'id'),
            'tab_controls' => [
                'base_item' => [
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'                => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => ['nullable', 'integer'],
                                    ],
                                    'is_enabled'        => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool'
                                        ],
                                        'css_group'    => 'col-6 col-md-3',
                                    ],
                                    'parameter_variant' => [
                                        'html_element' => 'select',
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\ViewTemplate::VALID_PARAMETER_VARIANTS),
                                        'label'        => __('Parameter Variant'),
                                        'description'  => __('Describes a set of variables should assigned to this view'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-6 col-md-9',
                                    ],
                                    'code'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Code'),
                                        'description'  => __('Code'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'view_file'         => [
                                        'html_element' => 'select_theme_files_in_folder',
                                        'options'      => [
                                            'path'            => 'views/notifications/emails',
                                            'directory_deep'  => 1,
                                            'regex_blacklist' => ['views/notifications/emails/inc'],
                                            'add_delimiters'  => '#',
                                        ],
                                        'label'        => __('View File'),
                                        'description'  => __("Force this content if set."),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'content'           => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Content'),
                                        'description'  => __("Ignored if a template was selected."),
                                        'options'      => [
                                            'rows' => 10,
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'description'       => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Description'),
                                        'description'  => __('Detailed description'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
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