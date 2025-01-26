@php
    use Illuminate\Http\Resources\Json\JsonResource;
    use Modules\Form\app\Forms\Base\ModelBase;
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\WebsiteBase\app\Models\MediaItem as MediaItemModel;
    use Modules\WebsiteBase\app\Forms\MediaItem as MediaItemForm;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var bool $visible maybe always true because we are here
     * @var bool $disabled enabled or disabled
     * @var bool $read_only disallow edit
     * @var bool $auto_complete auto fill user inputs
     * @var string $name name attribute
     * @var string $label label of this element
     * @var mixed $value value attribute
     * @var mixed $default default value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $css_group
     * @var string $x_model optional for alpine.js
     * @var string $livewire
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     * @var JsonResource $object
     * @var ModelBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
     */

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
    $objectModelId = (int)($object->id ?? 0);
    $mediaItemId = 0;
    if ($form_instance instanceof MediaItemForm) {
        $mediaItemId = $objectModelId;
    }
    if ($object->imageMaker ?? null) {
        $mediaItemId = (int)($object->imageMaker->id ?? 0);
    }
    if ($userId = $form_instance->getOwnerUserId()) {

    }
    $livewireKey = LivewireService::getKey('upload');
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @unless(empty($label))
        <label>{{ $label }}</label>
    @endunless

    <div>
        @if($objectModelId)
            @livewire('website-base::media-item-file-upload', [
            'objectModelId' => $objectModelId,
            'mediaItemId' => $mediaItemId,
            'userId' => $userId,
            'parentFormClass' => $form_instance::class,
            'parentFormLivewireClass' => $form_livewire::class,
            'parentFormLivewireId' => $form_livewire->getId(),
            'parentModelClass' => $form_instance->getObjectEloquentModelName(),
            'forceMediaType' => MediaItemModel::MEDIA_TYPE_IMAGE,
            ], key($livewireKey))
        @else
            <div class="bg-light text-danger p-4">
                {{ __("You can upload your image or media file here once you created the media item successfully.") }}
            </div>
        @endif
    </div>


    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>