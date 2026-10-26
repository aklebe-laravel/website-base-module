@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Acl\app\Models\AclResource;
    use Modules\Acl\app\Services\UserService;
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

    /**
     * @var BaseDataTable $this
     * @var Model $item
     * @var string $name
     * @var mixed $value
     **/

    /** @var UserService $userService */
    $userService = app(UserService::class);
    if(!$userService->currentUser()->hasAclResource(AclResource::RES_MANAGE_USERS)) {
        return;
    }
    $jsMessageBoxLaunchPath = app('system_base_module')->getModelSnakeName($this->getEloquentModelName()) . '.data-table.launch';

    $messageBoxParams1 = [
        'launch' => [
            'livewireId' => $this->getId(),
            'name' => $this->getName(),
            'itemId' => data_get($item, $this->columnNameId),
        ],
    ];
@endphp
<button class="btn btn-sm btn-outline-secondary mr-2 {{ data_get($this->mobileCssClasses, 'button', '') }}"
        x-on:click="messageBox.show('{{ $jsMessageBoxLaunchPath }}', {{ json_encode($messageBoxParams1) }} )"
        title="{{ __('Launch') }}"
>
    <x-lucide-play class="h-5 w-5"/>
</button>
