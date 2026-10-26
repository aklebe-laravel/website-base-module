<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

class CoreConfig extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    public array $objectRelations = ['store'];

    /**
     * Singular
     *
     * @var string
     */
    public string $objectFrontendLabel = 'Config';

    /**
     * Plural
     *
     * @var string
     */
    public string $objectsFrontendLabel = 'Configs';

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        return [
            ... $parentFormData,
            'tab_controls' => [
                'base_item' => [
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'          => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'store_id'    => [
                                        'html_element' => 'select',
                                        'label'        => __('Store'),
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\Store::orderBy('code',
                                            'ASC')->get(), [
                                            'id',
                                            'code',
                                        ],
                                            'id',
                                            app('system_base')->selectOptionsSimple[app('system_base')::selectValueNoChoice]),
                                        'description'  => __('The Store assigned to the category'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'path'        => [
                                        'html_element' => 'text',
                                        'label'        => __('Path'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'value'       => [
                                        'html_element' => function () {
                                            return $this->dataSource->form_input ?? 'textarea';
                                        },
                                        'label'        => __('Value'),
                                        'description'  => function () {
                                            return $this->dataSource->description ?? '';
                                        },
                                        'validator'    => function () {
                                            $meta = $this->elementMetaData[$this->dataSource->form_input] ?? [];
                                            $r = $meta['type'] ?? ['nullable', 'string'];
                                            $d = $this->dataSource;
                                            $v = data_get($d, 'value', false);
                                            $v = ($meta['cast'] ?? null) ? $meta['cast']() : $v;
                                            data_set($d, 'value', $v);

                                            return $r;
                                        },
                                        'css_group'    => 'form-group-responsive-full',
                                    ],
                                    'module'      => [
                                        'html_element' => 'text',
                                        'label'        => __('Module'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'position'    => [
                                        'html_element' => 'number',
                                        'label'        => __('Position'),
                                        'validator'    => [
                                            'required',
                                            'integer',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'description' => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Description'),
                                        'description'  => __('Description'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'form-group-responsive-full',
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
