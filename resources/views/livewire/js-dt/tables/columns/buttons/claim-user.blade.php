@php
    use Modules\Acl\app\Models\AclResource;
    use Modules\Acl\app\Services\UserService;
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

    /**
     * @var BaseDataTable $this
     * @var Illuminate\Database\Eloquent\Model $item
     * @var string $name
     * @var mixed $value
     **/

    $jsMessageBoxClaimItemPath = app('system_base_module')->getModelSnakeName($this->getEloquentModelName()) . '.data-table.claim';
    /** @var UserService $userService */
    $userService = app(UserService::class);

    $messageBoxParams1 = [
        'claim' => [
            'livewireId' => $this->getId(),
            'name' => $this->getName(),
            'itemId' => data_get($item, 'shared_id'),
            'currentUrl' => \Illuminate\Support\Facades\URL::current(),
        ],
    ];
@endphp
@if($userService->hasUserResource(\Illuminate\Support\Facades\Auth::user(), AclResource::RES_MANAGE_USERS))
    {{--Edit Button: If related form present--}}
    @if ($this->relatedLivewireForm)
        <button class="btn btn-sm btn-outline-secondary {{ data_get($this->mobileCssClasses, 'button', '') }}"
                title="{{ __('Claim User') }}"
                x-on:click="messageBox.show('{{ $jsMessageBoxClaimItemPath }}', {{ json_encode($messageBoxParams1) }} )"
        >
            <span class="bi bi-person"></span>
        </button>
    @endif
@endif
