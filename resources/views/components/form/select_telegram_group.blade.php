@php
    use Modules\TelegramApi\app\Services\TelegramService;

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
     * @var Illuminate\Http\Resources\Json\JsonResource $object
     * @var \Modules\Form\app\Forms\Base\ModelBase $form_instance
     */

    /** @var TelegramService $telegramService */
    $telegramService = app(TelegramService::class);
    if ($telegramGroups = $telegramService->getBotGroups()) {
        $telegramGroups = $telegramGroups->toArray();
    } else {
        $telegramGroups= [];
    }

@endphp
@include('form::components.form.select', [
    'options' => app('system_base')->toHtmlSelectOptions($telegramGroups, ['display_name'], 'id', [-1 => __('No choice')]),
    ])
