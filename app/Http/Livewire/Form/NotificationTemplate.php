<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

class NotificationTemplate extends ModelBase
{
    /**
     *
     * @var array[]
     */
    public array $objectRelations = [];

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
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled' => 1,
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
                                    'id'                   => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                    ],
                                    'is_enabled'           => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for listings.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'view_template_id'     => [
                                        'html_element' => 'select',
                                        'options'      => $systemService->toHtmlSelectOptions(\Modules\WebsiteBase\app\Models\ViewTemplate::orderBy('code',
                                            'ASC')->get(),
                                            ['code', 'view_file', 'id'],
                                            'id',
                                            $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]),
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
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'notification_channel' => $formService::getFormElementNotificationChannel(),
                                    'subject'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Subject'),
                                        'description'  => __('Subject'),
                                        'validator'    => [
                                            'required',
                                            'string',
                                            'Max:255',
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
