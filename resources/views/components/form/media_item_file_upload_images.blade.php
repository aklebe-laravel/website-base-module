@php
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\WebsiteBase\app\Http\Livewire\Form\MediaItem;
    use Modules\WebsiteBase\app\Models\MediaItem as MediaItemModel;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    /* @var JsonResource $object */
    $object = $form_instance->getDataSource();
    $objectModelId = (int)($object->id ?? 0);
    $mediaItemId = 0;
    if ($form_instance instanceof MediaItem) {
        $mediaItemId = $objectModelId;
    }
    if ($object->imageMaker ?? null) {
        $mediaItemId = (int)($object->imageMaker->id ?? 0);
    }
    if ($userId = $form_instance->getOwnerUserId()) {

    }
    $livewireKey = LivewireService::getKey('upload');
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <div>
        @if($objectModelId)
            @livewire('website-base::media-item-file-upload', [
            'objectModelId' => $objectModelId,
            'mediaItemId' => $mediaItemId,
            'userId' => $userId,
            'parentFormClass' => $form_instance::class,
            'parentFormLivewireClass' => $form_instance::class,
            'parentModelClass' => $form_instance->getObjectEloquentModelName(),
            'forceMediaType' => MediaItemModel::MEDIA_TYPE_IMAGE,
            ], key($livewireKey))
        @else
            <div class="bg-light text-danger p-4">
                {{ __("You can upload your image or media file here once you created the media item successfully.") }}
            </div>
        @endif
    </div>

    @include('form::components.form.element-parts.description')
</div>