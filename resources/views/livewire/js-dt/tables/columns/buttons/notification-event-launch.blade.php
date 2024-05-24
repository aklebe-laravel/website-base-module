@php
    /**
     * @var \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable $this
     * @var Illuminate\Database\Eloquent\Model $item
     * @var string $name
     * @var mixed $value
     **/

    use Modules\Acl\app\Services\UserService;

    /** @var UserService $userService */
    $userService = app(UserService::class);
    if(!$userService->hasUserResource(\Illuminate\Support\Facades\Auth::user(), \Modules\Acl\app\Models\AclResource::RES_MANAGE_USERS)) {
        return;
    }
    $jsMessageBoxLaunchPath = app('system_base_module')->getModelSnakeName($this->getModelName()) . '.data-table.launch';
@endphp
<button class="btn btn-sm btn-outline-secondary mr-2 {{ data_get($this->mobileCssClasses, 'button', '') }}"
        x-on:click="messageBox.show('{{ $jsMessageBoxLaunchPath }}', {'launch': {livewire_id: '{{ $this->getId() }}', name: '{{ $this->getName() }}', item_id: '{{ data_get($item, 'id') }}' }})"
        title="{{ __('Launch') }}"
>
    <span class="bi bi-file-play"></span>
</button>
