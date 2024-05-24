<?php

namespace Modules\WebsiteBase\app\Forms;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Form\app\Forms\Base\NativeObjectBase;

class Contact extends NativeObjectBase
{
    /**
     * Singular
     * @var string
     */
    protected string $objectFrontendLabel = 'Contact Message';

    /**
     * Plural
     * @var string
     */
    protected string $objectsFrontendLabel = 'Contact Messages';

    public function getJsonResource(mixed $id = null): JsonResource
    {
        $object = [
            'content' => '',
        ];

        $this->jsonResource = new JsonResource($object);
        return $this->jsonResource;
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
            'title'         => __(''),
            'form_elements' => [
                'content' => [
                    'html_element' => 'textarea',
                    'label'        => __('Content'),
                    'description'  => __('Content of your concern'),
                    'validator'    => [
                        'nullable',
                        'string',
                        'min:3'
                    ],
                    'css_group'    => 'col-12',
                ],
            ],

        ];
    }

}