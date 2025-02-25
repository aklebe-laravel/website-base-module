<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\WebsiteBase\app\Models\MediaItem as WebsiteMediaItemModel;

class MediaItem extends ModelBase
{
    /**
     * Prepared for inheritances
     *
     * @var string|null
     */
    protected ?string $objectEloquentModelName = WebsiteMediaItemModel::class;

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
    protected string $objectFrontendLabel = 'Media Item';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Media Items';

    /**
     * @var string
     */
    protected string $fixedMediaType = WebsiteMediaItemModel::MEDIA_TYPE_IMAGE;

    /**
     * @var string
     */
    protected string $defaultMediaType = '';

    /**
     * @var string
     */
    protected string $fixedObjectType = '';

    /**
     * @var string
     */
    protected string $defaultObjectType = WebsiteMediaItemModel::OBJECT_TYPE_PRODUCT_IMAGE;

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return array_merge(parent::makeObjectInstanceDefaultValues(), [
            'is_enabled'  => 1,
            'is_public'   => 0,
            'user_id'     => $this->getOwnerUserId(),
            'store_id'    => app('website_base_settings')->getStoreId(),
            'media_type'  => $this->fixedMediaType ?: $this->defaultMediaType,
            'object_type' => $this->fixedObjectType ?: $this->defaultObjectType,
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
            'title'        => $this->makeFormTitle($this->getDataSource(), 'name'),
            'css_classes'  => 'form-edit',
            //                        'x_model'       => 'object',
            'livewire'     => 'dataTransfer',
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
                                        'disabled'     => (bool) $this->fixedMediaType, // disable if fixed value
                                        'label'        => __('Media Type'),
                                        'options'      => [
                                            ... app('system_base')->toSelectOptionSimple('No choice'),
                                            ... WebsiteMediaItemModel::getMediaTypesAsSelectOptions(),
                                        ],
                                        'description'  => __('Type of this media'),
                                        'validator'    => ['nullable', 'string', 'Max:255'],
                                        'css_group'    => 'col-6',
                                    ],
                                    'object_type'       => [
                                        'html_element' => 'select',
                                        'disabled'     => (bool) $this->fixedObjectType, // disable if fixed value
                                        'label'        => __('Object Type'),
                                        'options'      => [
                                            ... app('system_base')->toSelectOptionSimple('No choice'),
                                            ... WebsiteMediaItemModel::getObjectTypesAsSelectOptions(),
                                        ],
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
                                        'html_element' => 'website-base::media_item_file_upload_images',
                                        'label'        => __('Media Upload'),
                                        'description'  => __('media_item_media_upload_description'),
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
                                        'label'        => __('Meta description'),
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

    /**
     * Its overwritten
     *
     * @param  mixed  $mediaItemId
     *
     * @return void
     */
    #[On('upload-process-finished')]
    public function uploadProcessFinished(mixed $mediaItemId): void
    {
        // do not fill relationUpdates here ...

        //
        $this->reopenFormIfNeeded();
    }
}
