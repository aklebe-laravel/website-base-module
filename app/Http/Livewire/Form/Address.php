<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\Form\app\Services\FormService;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;

class Address extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    public array $objectRelations = [];

    /**
     * Singular
     *
     * @var string
     */
    public string $objectFrontendLabel = 'Address';

    /**
     * Plural
     *
     * @var string
     */
    public string $objectsFrontendLabel = 'Addresses';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), [
            'user_id'     => $this->getOwnerUserId(),
            'country_iso' => 'DE',
            'city'        => 'Berlin',
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
            'title'        => $this->makeFormTitle($this->dataSource, 'id'),
            'tab_controls' => [
                'base_item' => [
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'               => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => ['nullable', 'integer'],
                                    ],
                                    'user_id'          => [
                                        'html_element' => 'hidden',
                                        'validator'    => ['required', 'integer'],
                                    ],
                                    'firstname'        => [
                                        'html_element' => 'text',
                                        'label'        => __('Firstname'),
                                        'validator'    => ['string', 'Max:80'],
                                        'css_group'    => '',
                                    ],
                                    'lastname'         => [
                                        'html_element' => 'text',
                                        'label'        => __('Lastname'),
                                        'validator'    => ['string', 'Max:80'],
                                        'css_group'    => '',
                                    ],
                                    'email'            => [
                                        'html_element' => 'email',
                                        'label'        => __('Email'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'phone'            => [
                                        'html_element' => 'text',
                                        'label'        => __('Phone'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:50',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'street'           => [
                                        'html_element' => 'text',
                                        'label'        => __('Street'),
                                        'validator'    => ['string', 'Max:50'],
                                        'css_group'    => '',
                                    ],
                                    'city'             => [
                                        'html_element' => 'text',
                                        'label'        => __('City'),
                                        'validator'    => ['string', 'Max:50'],
                                        'css_group'    => '',
                                    ],
                                    'zip'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Zip'),
                                        'validator'    => ['string', 'Max:5'],
                                        'css_group'    => '',
                                    ],
                                    'region'           => [
                                        'html_element' => 'text',
                                        'label'        => __('Region'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:50',
                                        ],
                                        'css_group'    => '',
                                    ],
                                    'country_iso'      => $formService->getFormElement(ExtraAttributeModel::ATTR_COUNTRY),
                                    'user_description' => [
                                        'html_element' => 'textarea',
                                        'label'        => __('UserDescription'),
                                        'description'  => __('UserDescriptionLong'),
                                        'validator'    => ['nullable', 'string', 'Max:30000'],
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
