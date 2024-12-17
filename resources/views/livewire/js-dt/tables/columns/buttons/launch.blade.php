@php
    /**
     * @var BaseDataTable $this
     * @var Illuminate\Database\Eloquent\Model $item
     * @var string $name
     * @var mixed $value
     **/

    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    $jsMessageBoxLaunchPath = app('system_base_module')->getModelSnakeName($this->getEloquentModelName()) . '.data-table.launch';
@endphp
<button class="btn btn-sm btn-outline-secondary mr-2 {{ data_get($this->mobileCssClasses, 'button', '') }}"
        x-on:click="messageBox.show('{{ $jsMessageBoxLaunchPath }}', {'launch': {livewire_id: '{{ $this->getId() }}', name: '{{ $this->getName() }}', item_id: '{{ data_get($item, 'id') }}' }})"
        title="{{ __('Launch') }}"
>
    <span class="bi bi-file-play"></span>
</button>
