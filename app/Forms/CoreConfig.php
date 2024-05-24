<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

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
    protected array $objectRelations = ['store'];

    /**
     * Singular
     * @var string
     */
    protected string $objectFrontendLabel = 'Config';

    /**
     * Plural
     * @var string
     */
    protected string $objectsFrontendLabel = 'Configs';

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
                                            'integer'
                                        ],
                                    ],
                                    'store_id'    => [
                                        'html_element' => 'select',
                                        'label'        => __('Store'),
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\Store::orderBy('code',
                                            'ASC')->get(), [
                                            'id',
                                            'code'
                                        ], 'id', [self::UNSELECT_RELATION_IDENT => __('No choice')]),
                                        'description'  => __('The Store assigned to the category'),
                                        'validator'    => [
                                            'nullable',
                                            'integer'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'path'        => [
                                        'html_element' => 'text',
                                        'label'        => __('Path'),
                                        'description'  => __('Path'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'value'       => [
                                        'html_element' => function () {
                                            return $this->jsonResource->resource->form_input ?? 'textarea';
                                        },
                                        'label'        => __('Value'),
                                        'description'  => function () {
                                            return $this->jsonResource->resource->description ?? '...';
                                        },
                                        'validator'    => function () {
                                            $r = ['nullable'];
                                            switch ($this->jsonResource->resource->form_input) {
                                                case 'switch':
                                                    $r[] = 'bool';
                                                    // @todo: replace this type cast in event after read model
                                                    $this->jsonResource->resource->value = (bool) $this->jsonResource->resource->value;
                                                    break;
                                            }
                                            return $r;
                                        },
                                        'css_group'    => 'col-12',
                                    ],
                                    'description' => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Description'),
                                        'description'  => __('Description'),
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