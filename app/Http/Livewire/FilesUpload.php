<?php

namespace Modules\WebsiteBase\app\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Modules\SystemBase\app\Http\Livewire\BaseComponent;
use Modules\WebsiteBase\app\Models\MediaItem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FilesUpload extends BaseComponent
{
    use WithFileUploads;

    /**
     * @var TemporaryUploadedFile[]
     */
    // #[Modelable]
    public $files = [];

    /**
     * If $userId is given, the MediaItem can be created if $mediaItemId is not present.
     *
     * @var int
     */
    public int $userId = 0;

    /**
     * If $mediaItemId is not given, we try to create the MediaItem if $userId is present.
     *
     * @var int
     */
    public int $mediaItemId = 0;

    /**
     * @var int
     */
    public int $objectModelId = 0;

    /**
     * If $parentModel is given, a new created MediaItem will read metadata.
     *
     * @var string
     */
    public string $parentFormClass = '';

    /**
     * If given, emit will be fired to update livewire
     *
     * @var string
     */
    public string $parentFormLivewireId = '';

    /**
     * @var string
     */
    public string $parentModelClass = '';

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('website-base::livewire.files-upload');
    }

    /**
     * Create media files inclusive thumbs, create MediaItem and assign relation.
     *
     * @param array $files
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function runUploadedMediaItems(array $files): array
    {
        $pathList = [];
        $tmpPath = Storage::path(config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp');
        if (!$this->mediaItemId && $this->objectModelId) {

            if ($this->userId) {

                $createData = [
                    'user_id'          => $this->userId,
                    'store_id'         => app('website_base_settings')->getStore()->getKey(),
                    'name'             => '123',
                    'description'      => '',
                    'meta_description' => '',
                    'media_type'       => MediaItem::MEDIA_TYPE_IMAGE,
                    'object_type'      => null,
                ];

                // will set if parent form is not MediaItem
                $mediaModelSyncRelation = '';

                if ($this->parentFormClass && $this->parentModelClass) {
                    $createData['media_type'] = ($this->parentModelClass)::MEDIA_TYPE;
                    $createData['object_type'] = ($this->parentModelClass)::MEDIA_OBJECT_TYPE;
                    $mediaModelSyncRelation = $this->parentFormClass::PARENT_RELATION_METHOD_NAME;
                }

                //                $mediaModel = MediaItem::factory()->create($createData);
                $mediaModel = app('media')->create($createData);
                if ($mediaModelSyncRelation) {
                    $mediaModel->$mediaModelSyncRelation()->sync([$this->objectModelId]);
                }
                $this->mediaItemId = $mediaModel->getKey();
            } else {
                Log::error('No user ID.', [__METHOD__]);
                return $pathList;
            }
        }

        /** @var MediaItem $mediaModel */
        if (!($mediaModel = MediaItem::find($this->mediaItemId))) {

            Log::error('Unable to load media item.', [$this->mediaItemId, __METHOD__]);

            return $pathList;
        }

        foreach ($files as $tmpFile) {

            $tmpFileFullPath = $tmpPath.'/'.$tmpFile;
            // @todo: multiple files into multiple $mediaModel's


            if (!file_exists($tmpFileFullPath)) {
                Log::error("Missing", [$tmpFileFullPath]);
                continue;
            }

            app('website_base_media')->createMediaFile($mediaModel, $tmpFileFullPath);
            //            $pathList[] = $tmpFileFullPath;
            $pathList[] = $mediaModel->final_url;
        }

        return $pathList;
    }

    /**
     * dispatch
     *
     * @param  string  $name
     * @param  array  $tmpFilenames
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[On('upload:finished')]
    public function finishUpload(string $name, array $tmpFilenames): void
    {
        // @todo: Better move to schedule?!
        $this->cleanupOldUploads();

        // Create media files inclusive thumbs, create MediaItem and assign relation.
        $list = $this->runUploadedMediaItems($tmpFilenames);

        // // Need to open the form again
        // $this->dispatch('open-form', $this->mediaItemId, false);

        // Need to open the form again, maybe more code is needed in event 'upload-process-finished'
        $this->dispatch('upload-process-finished', 'mediaItems', $this->mediaItemId);
    }
}
