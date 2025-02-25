@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
     */

    $itemId = $this->formObjectId;

    $messageBoxParamsDelete = [
        'cancel-account' => [
            'livewireId' => $this->getId(),
            'name' => $this->getName(),
            'itemId' => $itemId,
        ],
    ];
@endphp
@if ($itemId)
    @include('form::components.form.actions.defaults.default-button',[
        'buttonType' => 'alpine',
        'buttonLabel' => $buttonLabel ?? __("Cancel Account"),
        'buttonClick' => "messageBox.show('user.form.cancel-account', ".json_encode($messageBoxParamsDelete).")",
        'buttonCss' => 'btn-danger form-action-delete',
    ])
@endif
