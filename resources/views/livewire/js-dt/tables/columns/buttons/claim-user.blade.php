@php
    /**
     * @var \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable $this
     * @var Illuminate\Database\Eloquent\Model $item
     * @var string $name
     * @var mixed $value
     **/

    // path for messageBox.config
    use Modules\Acl\app\Services\UserService;

    $jsMessageBoxClaimItemPath = app('system_base_module')->getModelSnakeName($this->getModelName()) . '.data-table.claim';
    // $fullJsVarName = 'messageBox.config.' . $jsMessageBoxDeleteItemPath;
    /** @var UserService $userService */
    $userService = app(UserService::class);

@endphp
@if($userService->hasUserResource(\Illuminate\Support\Facades\Auth::user(), \Modules\Acl\app\Models\AclResource::RES_MANAGE_USERS))
    {{--Edit Button: If related form present--}}
    @if ($this->relatedLivewireForm)
        <button class="btn btn-sm btn-outline-secondary {{ data_get($this->mobileCssClasses, 'button', '') }}"
                title="{{ __('Claim User') }}"
                x-on:click="messageBox.show('{{ $jsMessageBoxClaimItemPath }}', {'claim': {livewire_id: '{{ $this->getId() }}', name: '{{ $this->getName() }}', item_id: '{{ data_get($item, 'shared_id') }}', current_url: '{{ \Illuminate\Support\Facades\URL::current() }}' }})"
        >
            <span class="bi bi-person"></span>
        </button>
    @endif
@endif
