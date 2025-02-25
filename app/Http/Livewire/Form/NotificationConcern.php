<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

class NotificationConcern extends ModelBase
{
    /**
     *
     * @var array[]
     */
    public array $objectRelations = [];

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
            'is_enabled' => 1,
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
        /** @var WebsiteBaseFormService $websiteBaseFormService */
        $websiteBaseFormService = app(WebsiteBaseFormService::class);
        /** @var SystemService $systemService */
        $systemService = app('system_base');

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
                                    'id'                       => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'is_enabled'               => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-4',
                                    ],
                                    //'store_id'                 => $websiteBaseFormService::getFormElementStore(),
                                    'store_id'                 => $formService->getFormElement(ExtraAttributeModel::ATTR_STORE),
                                    'notification_template_id' => [
                                        'html_element' => 'select',
                                        'options'      => $systemService->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\NotificationTemplate::orderBy('code',
                                            'ASC')->get(),
                                            ['code', 'notification_channel', 'id'],
                                            'id',
                                            $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]),
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
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'sender_id'                => $websiteBaseFormService::getFormElementPuppetUser([
                                        'label'       => __('Sender'),
                                        'description' => __('Sender Identity'),
                                    ]),
                                    'description'              => [
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
                                    'tags'                     => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Tags'),
                                        'description'  => __('Not working for all email clients.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
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
