@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')

    {{-- the picker loads its images itself (public ones and the own ones of the current user) --}}
    <livewire:website-base::image-picker
            wire:model="{{ $data['livewire'] .'.' . $data['name'] }}"
            wire:key="{{ $form_instance->getStableKey('image-picker')  }}"
    />

    @include('form::components.form.element-parts.description')
</div>
