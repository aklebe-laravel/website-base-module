@php
    use Illuminate\Http\Resources\Json\JsonResource;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    /* @var JsonResource $object */
    $object = $form_instance->getDataSource();

    $_formattedValue = '';
    if (!$object->resource->canLogin()) {
        if (!$object->is_enabled) {
            $_formattedValue .= ' '. __('Disabled');
        }
        if ($object->is_deleted) {
            $_formattedValue .= ' '. __('Deleted');
            $_formattedValue .= '<br>' . __('If this user was deleted, the user data were probably anonymized.');
        } elseif ($object->order_to_delete_at) {
            $_formattedValue .= ' ('. __('Prepared to delete') . ')';
        }
        $data['css_group'] .= ' alert alert-danger';
    } else {
        $data['css_group'] .= ' alert alert-success';
        $_formattedValue .= __('Valid');
    }
@endphp

<div class="form-group form-label-group {{ $data['css_group'] }}">
    <div class="form-control-info {{ $data['css_classes'] }}"
         class="form-control {{ $data['css_classes'] }}"
    >
        {!! $_formattedValue !!}
    </div>
    @unless(empty($data['description']))
        <div class="form-text decent">{{ $data['description'] }}</div>
    @endunless
</div>