@php
    use Modules\WebsiteBase\app\Models\Currency;
@endphp
@include('form::components.form.select', [
    'options' => app('system_base')->toHtmlSelectOptions(Currency::orderBy('name', 'ASC')->get(), ['name', 'code', 'symbol'], 'code', app('system_base')->selectOptionsSimple[app('system_base')::selectValueNoChoice]),
    ])
