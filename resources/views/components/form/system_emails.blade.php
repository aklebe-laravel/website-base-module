@php
    $list = [];
    /** @var \Illuminate\Database\Eloquent\Collection $siteUserAddresses */
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
    'options' => app('system_base')->toHtmlSelectOptions($list, ['label', 'id'], 'id', [\Modules\Form\app\Forms\Base\ModelBase::UNSELECT_RELATION_IDENT => __('No choice')]),
    ])
