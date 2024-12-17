@php
    /**
     * @var BaseDataTable $this
     * @var Illuminate\Database\Eloquent\Model $item
     * @var string $name
     * @var mixed $value
     **/

    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    $jsMessageBoxButtonPath = app('system_base_module')->getModelSnakeName($this->getEloquentModelName()) . '.data-table.import';
@endphp
<button class="btn btn-sm btn-outline-secondary mr-2 {{ data_get($this->mobileCssClasses, 'button', '') }}"
        x-on:click="messageBox.show('{{ $jsMessageBoxButtonPath }}', {'import': {livewire_id: '{{ $this->getId() }}', name: '{{ $this->getName() }}', item_id: '{{ data_get($item, 'id') }}' }})"
        title="{{ __('Import') }}"
>
    <span class="bi bi-upload"></span>
</button>
