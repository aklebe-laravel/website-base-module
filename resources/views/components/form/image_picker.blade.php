@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $attrs = $form_instance->calcInputAttributes($data);
    $name = $data['name'];
    $options = $data['options'];
    $options = collect($options)->map(fn($data2) => [
        'id' => $data2['id'],
        'label' => $data2['name'],
        'image' => $data2['final_thumb_medium_url'],
    ])->values()->toArray();
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')

    <livewire:website-base::image-picker
            :images="$options"
            wire:model="{{ $data['livewire'] .'.' . $data['name'] }}"
            wire:key="{{ $form_instance->getStableKey('image-picker')  }}"
    />

    @include('form::components.form.element-parts.description')
</div>

