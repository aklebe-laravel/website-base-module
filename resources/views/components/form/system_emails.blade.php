@php
    use Illuminate\Database\Eloquent\Collection;
    use Modules\Form\app\Forms\Base\NativeObjectBase;

    /** @var Collection $siteUserAddresses */

    $list = [];
    $siteUserAddresses = app('website_base_settings')->getSiteOwner()->addresses;
    $collection = $siteUserAddresses->sortBy('firstname', SORT_ASC);
    foreach ($collection as $item) {
        $list[] = [
            'id' => $item->email,
            'label' => $item->firstname,
        ];
    }
@endphp
@include('form::components.form.select', [
    'options' => app('system_base')->toHtmlSelectOptions($list, ['label', 'id'], 'id', app('system_base')->getHtmlSelectOptionNoValue('No choice', NativeObjectBase::UNSELECT_RELATION_IDENT)),
    ])
