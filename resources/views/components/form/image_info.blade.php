@php
    /**
     *
     * @var string $name
     * @var string $label
     * @var \Modules\WebsiteBase\app\Models\MediaItem $value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $x_model
     * @var string $xModelName
     * @var array $html_data
     * @var array $x_data
     */
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    {{ $label }}
    @if ($value->final_thumb_medium_url ?? null)
        <img src="{{ $value->final_thumb_medium_url }}"/>
    @else
        <div class="bg-light text-danger p-4">
            {{ __('No Image') }}
        </div>
    @endif
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>