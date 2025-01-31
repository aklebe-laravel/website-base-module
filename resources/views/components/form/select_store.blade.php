@php
    use Modules\SystemBase\app\Services\CacheService;
    use Modules\WebsiteBase\app\Models\Store;

    $options = app(CacheService::class)->rememberForever('form_element.select_store.options', function () {
        return app('system_base')->toHtmlSelectOptions(
            Store::orderBy('code','ASC')->get(),
            ['id','code'],
            'id',
            app('system_base')->toSelectOptionSimple('No choice')
            );
        });

@endphp
@include('form::components.form.select', ['cmpCi' => true, 'options' => $options])
