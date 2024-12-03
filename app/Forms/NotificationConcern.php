<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class NotificationConcern extends ModelBase
{
    /**
     *
     * @var array[]
     */
    protected array $objectRelations = [];

    /**
     * @var string
     */
    protected string $objectFrontendLabel = 'Notification Concern';

    /**
     * @var string
     */
    protected string $objectsFrontendLabel = 'Notification Concerns';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled' => true,
            'store_id'   => app('website_base_settings')->getStore()->getKey(),
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
                                    'id'                       => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer'
                                        ],
                                    ],
                                    'is_enabled'               => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool'
                                        ],
                                        'css_group'    => 'col-12 col-md-4',
                                    ],
                                    'store_id'                 => [
                                        'html_element' => 'website-base::store',
                                        'label'        => __('Store'),
                                        'description'  => __('Store'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-md-4',
                                    ],
                                    'notification_template_id' => [
                                        'html_element' => 'select',
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\NotificationTemplate::orderBy('code',
                                            'ASC')->get(), ['code', 'notification_channel', 'id'], 'id',
                                            [self::UNSELECT_RELATION_IDENT => __('No choice')]),
                                        'label'        => __('Notification Template'),
                                        'description'  => __('Notification template used as content.'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-md-4',
                                    ],
                                    'reason_code'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Reason'),
                                        'description'  => __('Reason'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'sender'                   => [
                                        'html_element' => 'website-base::system_emails',
                                        'label'        => __('Sender'),
                                        'description'  => __('Sender (Addresses of SiteOwner)'),
                                        'validator'    => [
                                            'nullable',
                                            'email',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'description'              => [
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
                                    'tags'                     => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Tags'),
                                        'description'  => __('Not working for all email clients.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'meta_data'                => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Meta Data'),
                                        'description'  => __('Not working for all email clients.'),
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