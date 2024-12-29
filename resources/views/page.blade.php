@php
    /**
     * @var string $modelName like "User" (@todo: only uses for bottom javascript, wich is used for image uploads)
     * @var string $livewireForm like "website-base::form.user"
     * @var string $livewireTable like "website-base::data-table.user"
     * @var string|array $contentView
     * @var array $contentViewParams
     */

    use Modules\Acl\app\Http\Middleware\AdminUserPresent;
    use Modules\Acl\app\Http\Middleware\StaffUserPresent;

    $contentViewParams = $contentViewParams ?? [];
    $isAdminPage = in_array(AdminUserPresent::class, \Illuminate\Support\Facades\Route::current()->computedMiddleware);
    $isStaffPage = in_array(StaffUserPresent::class, \Illuminate\Support\Facades\Route::current()->computedMiddleware);
@endphp
@unless(empty($title))
    @section('title', __($title))
@endunless
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight {{ $isAdminPage ? 'admin-header text-danger' : ($isStaffPage ? 'text-warning staff-header' : '' ) }}">
            @yield('title')
        </h2>
    </x-slot>
    @include('website-base::page-content-form')
</x-app-layout>