@php
    /**
     *
     * @var string $name
     * @var string $label
     * @var mixed $value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $x_model
     * @var string $xModelName
     * @var array $html_data
     * @var array $x_data
     * @var mixed $validator
     * @var string $css_group
     * @var Illuminate\Http\Resources\Json\JsonResource $object
     * @var \Modules\Form\app\Forms\Base\ModelBase $form_instance
     */

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
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
        $css_group .= ' alert alert-danger';
    } else {
        $css_group .= ' alert alert-success';
        $_formattedValue .= __('Valid');
    }
    // $_formattedValue = print_r($validator, true);
    // dump($object->resource->toArray(), true);
@endphp

<div class="form-group form-label-group {{ $css_group }}">
    <div class="form-control-info {{ $css_classes }}"
         class="form-control {{ $css_classes }}"
         @if($xModelName) x-model="{{ $xModelName }}" @endif
         @if($disabled) disabled="disabled" @endif
         @if($read_only) read_only @endif
         @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
         @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach

    >
        {!! $_formattedValue !!}
    </div>
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>