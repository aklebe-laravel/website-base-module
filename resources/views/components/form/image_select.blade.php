@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $attrs = $form_instance->calcInputAttributes($data);
    $name = $data['name'];
    $options = $data['options'];
    //dump($options);
    $options = collect($options)->map(fn($data) => [
        'id' => $data['id'],
        'label' => $data['name'],
        'image' => $data['final_thumb_small_url'],
    ])->values()->toArray();
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')

    <x-select.styled
            :attributes="$attrs"
            :options="$options"
            select="label:label|value:id"
    >
        <x-slot:option name="option">
            <div class="flex items-center gap-2">
                <!-- Accessing properties directly via the components scoped option array -->
                <img ::src="option.image"
                     ::alt="option.label"
                     class="h-6 w-6 rounded-full object-cover">

                <span class="text-surface-700" x-text="option.label"></span>
            </div>
        </x-slot:option>
    </x-select.styled>

    @include('form::components.form.element-parts.description')
</div>
