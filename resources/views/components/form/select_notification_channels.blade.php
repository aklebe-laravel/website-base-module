@php
    use Illuminate\Http\Resources\Json\JsonResource;use Modules\Form\app\Forms\Base\ModelBase;
    use Modules\WebsiteBase\app\Services\SendNotificationService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * default input text element
     *
     * @var bool $visible maybe always true because we are here
     * @var bool $disabled enabled or disabled
     * @var bool $read_only disallow edit
     * @var bool $auto_complete auto fill user inputs
     * @var string $name name attribute
     * @var string $label label of this element
     * @var mixed $value value attribute
     * @var mixed $default default value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $css_group
     * @var string $x_model optional for alpine.js
     * @var string $livewire
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     * @var JsonResource $object
     * @var ModelBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
     */

    $systemService = app('system_base');
    $sortedChannels = $object->getExtraAttribute('preferred_notification_channels', []);
    $registeredChannels = app(SendNotificationService::class)->getRegisteredChannelNames();
    $sortedChannels = array_merge($sortedChannels, $registeredChannels);
@endphp
@include('form::components.form.sortable_multi_select', [
    'options' => app('system_base')->toHtmlSelectOptions(
        $sortedChannels),
    ])
