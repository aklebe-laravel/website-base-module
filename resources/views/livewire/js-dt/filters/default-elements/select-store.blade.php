@php
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

    /** @var BaseDataTable $this */
    /** @var string $collectionName */
    /** @var array $_config config for this data */
@endphp
<select wire:model.live.debounce="filters.{{ $collectionName }}.{{ $_config['name'] }}"
        class="form-control {{ $_config['css_item'] }} {{ $this->isFilterDefault($collectionName, $_config['name']) ? '' : 'bg-warning-subtle' }}"
>
    @foreach($_config['options'] as $k => $v)
        <option value="{{ $k }}">{{ is_array($v) ? data_get($v, 'label') : $v }}</option>
    @endforeach
</select>
