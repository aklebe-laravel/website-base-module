<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

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
    public array $objectRelations = [
        'notificationConcerns',
        'aclResources',
        'users',
    ];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Notification Event';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Notification Events';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled'    => 1,
            'repeat_count'  => 0,
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

        /** @var WebsiteBaseFormService $formService */
        $formService = app(WebsiteBaseFormService::class);
        /** @var SystemService $systemService */
        $systemService = app('system_base');

        $defaultSettings = $this->getDefaultFormSettingsByPermission();

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
                                    'id'            => [
                                        'html_element' => 'hidden',
                                        'label'        => 'ID',
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'is_enabled'    => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'event_trigger' => [
                                        'html_element' => 'select',
                                        'options'      => $systemService->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\NotificationEvent::VALID_EVENT_TRIGGERS),
                                        'label'        => __('Trigger'),
                                        'description'  => __('Event Trigger'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
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
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'event_code'    => [
                                        'html_element' => 'select',
                                        'options'      => $systemService->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\NotificationEvent::VALID_EVENT_CODES),
                                        'label'        => __('Event Code'),
                                        'description'  => __('Specific logic depends on this code'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'force_channel' => $formService::getFormElementNotificationChannel(),
                                    'subject'       => [
                                        'html_element' => 'text',
                                        'label'        => __('Subject'),
                                        'description'  => __("Ignored if a template was selected."),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
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
                                            'Max:30000',
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
                                            'Max:255',
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
                                            'Max:255',
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
                                            'Max:30000',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                ],
                            ],
                        ],
                        [
                            // don't show if creating a new object ...
                            'disabled' => !$this->getDataSource()->getKey(),
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
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            // don't show if creating a new object ...
                            'disabled' => !$this->getDataSource()->getKey(),
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
                                                'description'         => "notification_event_user_list_description",
                                                'filterByParentOwner' => false,
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            // don't show if creating a new object ...
                            'disabled' => !$this->getDataSource()->getKey(),
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
                                                'description'         => "notification_event_user_list_description",
                                                'filterByParentOwner' => false,
                                            ],
                                        ],
                                        'validator'    => [
                                            'nullable',
                                            'array',
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
