<?php

namespace Modules\WebsiteBase\app\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Modules\SystemBase\app\Http\Livewire\BaseComponent;
use Modules\WebsiteBase\app\Models\Base\TraitBaseMedia;
use Modules\WebsiteBase\app\Models\MediaItem;
use Modules\WebsiteBase\app\Models\User;

class MediaItemFileUpload extends BaseComponent
{
    use WithFileUploads;

    /**
     * @var TemporaryUploadedFile[]
     */
    // #[Modelable]
    public array $files = [];

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
     * @var string
     */
    public string $forceMediaType = ''; //MediaItem::MEDIA_TYPE_IMAGE;

    /**
     * @return Application|Factory|View
     */
    public function render(): Factory|View|Application
    {
        return view('website-base::livewire.media-item-file-upload', [
            'acceptExtensions' => MediaItem::getMediaTypeExtensionsForHtml($this->forceMediaType),
        ]);
    }

    /**
     * Create media files inclusive thumbs, create MediaItem and assign relation.
     * If in MediaItem Form, the image will be changed in the existing media item.
     * Otherwise, the uploaded image always will create a new media item and set up the MAKER.
     *
     * @param  array  $files
     *
     * @return array
     */
    private function runUploadedMediaItems(array $files): array
    {
        $pathList = [];
        $tmpPath = Storage::path(config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp');

        // Check it's from a MediaItem form or a form like Product or User to make a quick upload ...
        $protoParentModelInstance = app($this->parentModelClass);
        $isMediaItemForm = ($protoParentModelInstance instanceof MediaItem);

        // 1) objectModelId always must exist here
        // 2) If not a MediaItem Form, we always force to create new Media item. Otherwise, we just change the image.
        if ($this->objectModelId && (!$isMediaItemForm || !$this->mediaItemId)) {

            if ($this->userId) {

                $createData = [
                    'user_id'          => $this->userId,
                    'store_id'         => app('website_base_settings')->getStore()->getKey(),
                    'name'             => '123',
                    'description'      => '',
                    'meta_description' => '',
                    'media_type'       => $this->forceMediaType ?: null,
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

                    /** @var TraitBaseMedia $x */
                    foreach ($mediaModel->$mediaModelSyncRelation()->get() as $x) {
                        if ($x->getKey() == $this->objectModelId) {
                            $x->saveContentImage(User::IMAGE_MAKER, $mediaModel->getKey());
                        }
                    }
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

            // create the media file using temp file ...
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
     * @param  array   $tmpFilenames
     *
     * @return void
     */
    #[On('upload:finished')]
    public function finishUpload(string $name, array $tmpFilenames): void
    {
        // cleanup temp uploads by framework
        $this->cleanupOldUploads();

        // Create media files inclusive thumbs, create MediaItem and assign relation.
        $list = $this->runUploadedMediaItems($tmpFilenames);

        // // Need to open the form again
        // $this->reopenFormIfNeeded();

        // Need to open the form again, maybe more code is needed in event 'upload-process-finished'
        $this->dispatch('upload-process-finished', 'mediaItems', $this->mediaItemId);
    }
}
