@php
    use Modules\WebsiteBase\app\Models\Store;
@endphp
@include('form::components.form.select', [
    'options' => app('system_base')->toHtmlSelectOptions(Store::orderBy('code', 'ASC')->get(), ['id', 'code'], 'id', [-1 => __('No choice')]),
    ])
