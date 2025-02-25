<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\Form\app\Services\FormService;

class ViewTemplate extends ModelBase
{
    /**
     *
     * @var array[]
     */
    public array $objectRelations = [];

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
            'is_enabled'        => 1,
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

        /** @var FormService $formService */
        $formService = app(FormService::class);

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->getDataSource(), 'id'),
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
                                            'bool',
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
                                            'Max:255',
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
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'view_file'         => $formService::getFormElementFormThemeFile([
                                        'path'            => 'views/notifications',
                                        'directory_deep'  => 2,
                                        'regex_blacklist' => ['views/notifications/.*?/inc'],
                                        'add_delimiters'  => '#',
                                    ]),
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
                                            'Max:255',
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
