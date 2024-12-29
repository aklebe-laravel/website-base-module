@php
    use Modules\WebsiteBase\app\Models\Currency;
    use Modules\Form\app\Forms\Base\NativeObjectBase;
@endphp
@include('form::components.form.select', [
    'options' => app('system_base')->toHtmlSelectOptions(Currency::orderBy('name', 'ASC')->get(), ['name', 'code', 'symbol'], 'code', app('system_base')->getHtmlSelectOptionNoValue('No choice', NativeObjectBase::UNSELECT_RELATION_IDENT)),
    ])
