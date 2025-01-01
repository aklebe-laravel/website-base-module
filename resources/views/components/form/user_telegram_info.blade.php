@php
    use Illuminate\Http\Resources\Json\JsonResource;
    use Modules\Form\app\Forms\Base\ModelBase;
    use Modules\SystemBase\app\Services\ModuleService;
    use Modules\TelegramApi\app\Services\TelegramService;
    use Modules\WebsiteBase\app\Forms\UserProfile;
    use Modules\WebsiteBase\app\Services\WebsiteTelegramService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     *
     * @var string $name
     * @var string $label
     * @var string $value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $x_model
     * @var string $xModelName
     * @var string $livewire
     * @var array $html_data
     * @var array $x_data
     * @var JsonResource $object
     * @var ModelBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
     */

    /** @var ModuleService $moduleService */
    $moduleService = app(ModuleService::class);
    $_telegramId = null;
    if ($moduleTelegramExists = $moduleService->moduleExists('TelegramApi')) {
        /** @var WebsiteTelegramService $websiteTelegramService */
        $websiteTelegramService = app(WebsiteTelegramService::class);
        /** @var TelegramService $telegramService */
        $telegramService = app(TelegramService::class);
        $_telegramBot = $telegramService->getDefaultBotName();

        $_useTelegram = $object->getExtraAttribute('use_telegram');
        $_telegramId = $object->getExtraAttribute('telegram_id');
        // $_telegramValid = ($_useTelegram && $_telegramId);
        $_telegramIdentityModel = \Modules\TelegramApi\app\Models\TelegramIdentity::with([])->where('telegram_id', $_telegramId)->first();
    }

@endphp
<div wire:ignore.self
     class="form-group form-label-group p-4 {{ $css_group }} {{ $_telegramId ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
    @if ($moduleTelegramExists)
        @if ($_telegramId)
            <div class="{{ $_telegramId ? 'bg-success-subtle' : 'bg-warning-subtle' }}">
                {{ $label }}:
                <span class="bi bi-check"></span>
                <span class="">{{ $_telegramIdentityModel ? $_telegramIdentityModel->display_name : $_telegramId }}</span>
            </div>
        @else
            <div class="text-danger">
                {{ $label }}:
                {{ __('Not in use.') }}
            </div>
        @endif
        <div class="mt-2">
            @if ($websiteTelegramService->isTelegramEnabled() && $_telegramBot)
                @if ($_telegramId)
                    <button class="btn btn-danger"
                            x-on:click="messageBox.show('telegram.login.delete', {'telegram-delete-me': {livewire_id: '{{ $form_livewire->getId() }}', name: 'website-base::form.user-profile', item_id: {{ $object->getKey() }}}})"
                    >
                        {{ __('Delete Telegram ID ...') }}
                    </button>
                @else
                    <div class="pt-5 pb-2">
                        @if($form_instance instanceof UserProfile)
                            <div class="telegram-login">
                                <script async src="https://telegram.org/js/telegram-widget.js?22"
                                        data-telegram-login="{{ $_telegramBot }}"
                                        data-size="large" data-onauth="onTelegramAuth(user)"
                                        data-request-access="write"></script>
                                <script type="text/javascript">
                                    function onTelegramAuth(user) {
                                        Livewire.dispatchTo('website-base::form.user-profile', 'telegram-assign-me', user);
                                    }
                                </script>
                            </div>
                        @endif
                        <div class="p-4">
                            <p>
                                {{ __("telegram_connect_info") }}
                            </p>
                            <p class="decent">
                                {{ __("telegram_privacy_data_info") }}
                            </p>
                        </div>
                    </div>
                @endif
            @else
                <spam>{{ __('Telegram disabled') }}</spam>
            @endif
        </div>
    @else
        <span>{{ __('Telegram Module not found.') }}</span>
    @endif
</div>