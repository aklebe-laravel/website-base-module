@php
    use Modules\SystemBase\app\Services\CacheService;
    use Modules\WebsiteBase\app\Models\Country;

    $options = app(CacheService::class)->rememberForever('form_element.select_country.options', function () {
        return app('system_base')->toHtmlSelectOptions(
            Country::orderBy('nice_name', 'ASC')->selectRaw('*, LOWER(iso) as iso')->get(),
            ['nice_name', 'iso'],
            'iso',
            app('system_base')->toSelectOptionSimple('No choice')
            );
        });

@endphp
@include('form::components.form.select', ['cmpCi' => true, 'options' => $options])
