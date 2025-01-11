@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

    /**
     * @var BaseDataTable $this
     * @var Model $item
     * @var string $name
     * @var mixed $value
     **/

    $jsMessageBoxButtonPath = app('system_base_module')->getModelSnakeName($this->getEloquentModelName()) . '.data-table.upload';

    $messageBoxParams1 = [
        'upload' => [
            'livewireId' => $this->getId(),
            'name' => $this->getName(),
            'itemId' => data_get($item, $this->columnNameId),
        ],
    ];
@endphp
<button class="btn btn-sm btn-outline-secondary mr-2 {{ data_get($this->mobileCssClasses, 'button', '') }}"
        x-on:click="messageBox.show('{{ $jsMessageBoxButtonPath }}', {{ json_encode($messageBoxParams1) }} )"
        title="{{ __('Upload') }}"
>
    <span class="bi bi-upload"></span>
</button>
