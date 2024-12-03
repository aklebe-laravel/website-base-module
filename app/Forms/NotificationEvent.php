<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class NotificationEvent extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    protected array $objectRelations = [
        'notificationConcerns',
        'aclResources',
        'users',
    ];

    /**
     * Singular
     * @var string
     */
    protected string $objectFrontendLabel = 'Notification Event';

    /**
     * Plural
     * @var string
     */
    protected string $objectsFrontendLabel = 'Notification Events';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled'    => true,
            'repeat_count'  => false,
            'event_code'    => \Modules\WebsiteBase\app\Models\NotificationEvent::EVENT_CODE_NOTIFY_USERS,
            'event_trigger' => \Modules\WebsiteBase\app\Models\NotificationEvent::EVENT_TRIGGER_MANUALLY,
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
                                    'id'            => [
                                        'html_element' => 'hidden',
                                        'label'        => 'ID',
                                        'validator'    => [
                                            'nullable',
                                            'integer'
                                        ],
                                    ],
                                    'is_enabled'    => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'event_trigger' => [
                                        'html_element' => 'select',
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\NotificationEvent::VALID_EVENT_TRIGGERS),
                                        'label'        => __('Trigger'),
                                        'description'  => __('Event Trigger'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'name'          => [
                                        'html_element' => 'text',
                                        'label'        => __('Name'),
                                        'description'  => __('Name'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'event_code'    => [
                                        'html_element' => 'select',
                                        'options'      => app('system_base')->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\NotificationEvent::VALID_EVENT_CODES),
                                        'label'        => __('Event Code'),
                                        'description'  => __('Specific logic depends on this code'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'force_channel' => [
                                        'html_element' => 'website-base::select_notification_channel',
                                        'label'        => __('Force Notification Channel'),
                                        'description'  => __('If not empty: Always use this channel. If user cannot use or don\'t preferred this channel , this message will not send to him.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'subject'       => [
                                        'html_element' => 'text',
                                        'label'        => __('Subject'),
                                        'description'  => __("Ignored if a template was selected."),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'content'       => [
                                        'html_element' => 'textarea',
                                        'options'      => [
                                            'rows' => 10,
                                        ],
                                        'label'        => __('Content'),
                                        'description'  => __("Ignored if a template was selected."),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'event_data'    => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Event Data'),
                                        'description'  => __('Event code specific extra data as json'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'content_data'  => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Content Data'),
                                        'description'  => __('Extra json data related to the content above'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'description'   => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Description'),
                                        'description'  => __('Description'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                ],
                            ],
                        ],
                        [
                            // don't show if creating a new object ...
                            'disabled' => !$this->jsonResource->getKey(),
                            'tab'      => [
                                'label' => __('Notification Concerns'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'notificationConcerns' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Notification Concerns'),
                                        'description'  => __('Notification Concerns linked to this group'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'  => 'website-base::form.notification-concern',
                                            'table' => 'website-base::data-table.notification-concern',
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            // don't show if creating a new object ...
                            'disabled' => !$this->jsonResource->getKey(),
                            'tab'      => [
                                'label' => __('Users'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'users' => [
                                        'html_element' => $defaultSettings['element_dt'],
                                        'label'        => __('Users'),
                                        'description'  => __('Users linked to this group'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'website-base::form.user',
                                            'table'         => 'website-base::data-table.user',
                                            'table_options' => [
                                                'description'             => "notification_event_user_list_description",
                                                'useCollectionUserFilter' => false,
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            // don't show if creating a new object ...
                            'disabled' => !$this->jsonResource->getKey(),
                            'tab'      => [
                                'label' => __('Acl Resources'),
                            ],
                            'content'  => [
                                'form_elements' => [
                                    'aclResources' => [
                                        'html_element' => 'element-dt-split-default',
                                        'label'        => __('Acl Resources'),
                                        'description'  => __('Acl Resources'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'table'         => 'acl::data-table.acl-resource',
                                            'table_options' => [
                                                'description'             => "notification_event_user_list_description",
                                                'useCollectionUserFilter' => false,
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array'
                                        ],
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