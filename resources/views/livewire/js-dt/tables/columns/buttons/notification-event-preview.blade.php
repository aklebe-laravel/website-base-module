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
@endphp
<a class="btn btn-sm btn-outline-secondary mr-2 {{ data_get($this->mobileCssClasses, 'button', '') }}"
   href="{{ route('preview-notify-event', data_get($item, 'id')) }}" target="_blank"
   title="{{ __('Preview') }}">
    <span class="bi bi-eyeglasses"></span>
</a>
