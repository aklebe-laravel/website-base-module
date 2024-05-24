@php
    /**
     * @var string $modelName like "User" (@todo: only uses for bottom javascript, wich is used for image uploads)
     * @var string $livewireForm like "market-form-user"
     * @var string $livewireTable like "market-data-table-user"
     * @var string|array $contentView
     * @var array $contentViewParams
     */
    $contentViewParams = $contentViewParams ?? [];
//    dump(\Illuminate\Support\Facades\Route::current()->computedMiddleware);
    $isAdminPage = in_array(\Modules\Acl\app\Http\Middleware\AdminUserPresent::class, \Illuminate\Support\Facades\Route::current()->computedMiddleware);
    $isStaffPage = in_array(\Modules\Acl\app\Http\Middleware\StaffUserPresent::class, \Illuminate\Support\Facades\Route::current()->computedMiddleware);
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
    <div class="py-12 website-base-dt-forms" @if ($modelName ?? null) x-data="getNewForm('{{ $modelName }}')" @endif>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                @error('name'){{ $message }}@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                @if (is_array($contentView))
                                    @foreach($contentView as $contentViewItem)
                                        @include($contentViewItem)
                                    @endforeach
                                @else
                                    @include($contentView)
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>