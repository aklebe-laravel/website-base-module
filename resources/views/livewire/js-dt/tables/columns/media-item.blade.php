@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

    /**
     * @var BaseDataTable $this
     * @var Model $item
     * @var string $name
     * @var mixed $value The image path
     * @var string $link make clickable link if exists
     * @var string $imageBoxCss image-box css
     **/
    $imageBoxCss = $imageBoxCss ?? '';
    $value = $item->mediaItem->final_thumb_small_url ?? null;
    $value = $value ?: theme_asset('images/no_image_available.webp');
@endphp
<div class="text-center image-content w-full">
    <div class="image-box {{ $imageBoxCss }}">
        <img src="{{ $value }}" alt="{{ $title ?? '' }}" title="{{ $title ?? '' }}" class="rounded"/>
    </div>
</div>

