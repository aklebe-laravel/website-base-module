<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class NotificationTemplate extends ModelBase
{
    /**
     *
     * @var array[]
     */
    protected array $objectRelations = [];

    /**
     * @var string
     */
    protected string $objectFrontendLabel = 'Notification Template';

    /**
     * @var string
     */
    protected string $objectsFrontendLabel = 'Notification Templates';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled' => true,
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
                                    'id'                   => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer'
                                        ],
                                    ],
                                    'is_enabled'           => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'view_template_id'     => [
                                        'html_element' => 'select',
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\ViewTemplate::orderBy('code',
                                            'ASC')->get(), ['code', 'view_file', 'id'], 'id',
                                            app('system_base')->getHtmlSelectOptionNoValue('No choice', self::UNSELECT_RELATION_IDENT)),
                                        'label'        => __('View Template'),
                                        'description'  => __('View template used as content.'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'code'                 => [
                                        'html_element' => 'text',
                                        'label'        => __('Code'),
                                        'description'  => __('Code'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'notification_channel' => [
                                        'html_element' => 'website-base::select_notification_channel',
                                        'label'        => __('Notification Channel'),
                                        'description'  => __('Notification Channel Description'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'subject'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Subject'),
                                        'description'  => __('Subject'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'description'          => [
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