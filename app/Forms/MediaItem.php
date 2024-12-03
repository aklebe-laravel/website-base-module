<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class MediaItem extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    protected array $objectRelations = [];

    /**
     * Singular
     * @var string
     */
    protected string $objectFrontendLabel = 'Media Item';

    /**
     * Plural
     * @var string
     */
    protected string $objectsFrontendLabel = 'Media Items';

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled'  => true,
            'is_public'   => false,
            'user_id'     => $this->getOwnerUserId(),
            'store_id'    => app('website_base_settings')->getStore()->getKey() ?? null,
            'media_type'  => \Modules\WebsiteBase\app\Models\MediaItem::MEDIA_TYPE_IMAGE,
            'object_type' => \Modules\WebsiteBase\app\Models\MediaItem::OBJECT_TYPE_PRODUCT_IMAGE,
            'position'    => 100,
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
            'title'        => $this->makeFormTitle($this->jsonResource, 'name'),
            'css_classes'  => 'form-edit',
            //                        'x_model'       => 'object',
            'livewire'     => 'formObjectAsArray',
            'tab_controls' => [
                'base_item' => [
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'                => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => ['nullable', 'integer'],
                                    ],
                                    'user_id'           => [
                                        'html_element' => 'hidden',
                                        'validator'    => ['required', 'integer'],
                                    ],
                                    'name'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Name'),
                                        'description'  => __('Product name'),
                                        'validator'    => ['required', 'string', 'Max:255'],
                                        'css_group'    => 'col-10',
                                    ],
                                    'position'          => [
                                        'html_element' => 'text',
                                        'label'        => __('Position'),
                                        'description'  => __('Order in listings'),
                                        'validator'    => ['nullable', 'integer'],
                                        'css_group'    => 'col-2',
                                    ],
                                    'media_type'        => [
                                        'html_element' => 'select',
                                        'label'        => __('Media Type'),
                                        'options'      => \Modules\WebsiteBase\app\Models\MediaItem::getMediaTypesAsSelectOptions(),
                                        'description'  => __('Type of this media'),
                                        'validator'    => ['nullable', 'string', 'Max:255'],
                                        'css_group'    => 'col-6',
                                    ],
                                    'object_type'       => [
                                        'html_element' => 'select',
                                        'label'        => __('Object Type'),
                                        'options'      => \Modules\WebsiteBase\app\Models\MediaItem::getObjectTypesAsSelectOptions(),
                                        'description'  => __('What should this media used for'),
                                        'validator'    => ['nullable', 'string', 'Max:255'],
                                        'css_group'    => 'col-6',
                                    ],
                                    'final_url'         => [
                                        'html_element' => 'image',
                                        'label'        => __('Image'),
                                        'description'  => __('Image'),
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'media_file_upload' => [
                                        'html_element' => 'website-base::file_upload',
                                        'label'        => __('Media Upload'),
                                        'description'  => __('Media Upload'),
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'description'       => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Description'),
                                        'description'  => __('Leave a short description of the media item'),
                                        'validator'    => ['nullable', 'string', 'Max:255'],
                                        'css_group'    => 'col-12',
                                    ],
                                    'meta_description'  => [
                                        'html_element' => 'textarea',
                                        'label'        => __('Meta Description'),
                                        'description'  => __('Meta description of the media item'),
                                        'validator'    => ['nullable', 'string', 'Max:255'],
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