<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Livewire\Attributes\On;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\BaseWebsiteBaseDataTable;
use Modules\WebsiteBase\app\Models\MediaItem as WebsiteMediaItemModel;

class MediaItemImport extends MediaItem
{
    use BaseWebsiteBaseDataTable;

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
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.name.label', __('Import Name'));
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.media_type.visible', false);
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.object_type.css_group', 'col-12');
        data_set($parentFormData, 'tab_controls.base_item.tab_pages.0.content.form_elements.media_file_upload', [
            'html_element' => 'website-base::media_item_file_upload_imports',
            'label'        => __('Upload Import Files'),
            'description'  => __('Upload Import Files'),
            'css_group'    => 'col-12',
        ]);

        $parentFormData['description'] = __('import_description')."\n\n".$parentFormData['description'];

        return $parentFormData;
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but before any update methods are called
     *
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        $this->addBaseWebsiteMessageBoxes();
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
