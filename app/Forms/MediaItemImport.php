<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\WebsiteBase\app\Models\MediaItem as WebsiteMediaItemModel;

/**
 *
 */
class MediaItemImport extends MediaItem
{
    /**
     * @var string
     */
    protected string $fixedMediaType = WebsiteMediaItemModel::MEDIA_TYPE_IMPORT;

    /**
     * @var string
     */
    protected string $defaultObjectType = WebsiteMediaItemModel::OBJECT_TYPE_IMPORT_PRODUCT;
    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        data_forget($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.final_url');
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.media_type.visible', false);
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.object_type.css_group', 'col-12');
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.media_file_upload', [
            'html_element' => 'website-base::media_item_file_upload_imports',
            'label'        => __('Upload Import Files'),
            'description'  => __('Upload Import Files'),
            'css_group'    => 'col-12',
        ]);

        return $parentFormData;
    }

}