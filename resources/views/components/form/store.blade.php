@php
    use Modules\WebsiteBase\app\Models\Store;
    use Modules\Form\app\Forms\Base\NativeObjectBase;
@endphp
@include('form::components.form.select', [
    'options' => app('system_base')->toHtmlSelectOptions(Store::orderBy('code', 'ASC')->get(), ['id', 'code'], 'id', app('system_base')->getHtmlSelectOptionNoValue('No choice', NativeObjectBase::UNSELECT_RELATION_IDENT)),
    ])
