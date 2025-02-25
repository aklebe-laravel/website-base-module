<?php

namespace Modules\WebsiteBase\app\Listeners;

use Modules\Form\app\Events\InitFormElements as InitFormElementsEvent;
use Modules\Form\app\Services\FormService;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

class InitFormElements
{
    /**
     * @param  InitFormElementsEvent  $event
     *
     * @return void
     */
    public function handle(InitFormElementsEvent $event): void
    {
        /** @var FormService $formService */
        $formService = app(FormService::class);
        /** @var WebsiteBaseFormService $websiteBaseFormService */
        $websiteBaseFormService = app(WebsiteBaseFormService::class);

        // attribute codes
        $formService->registerFormElement(ExtraAttributeModel::ATTR_STORE, fn($x) => $websiteBaseFormService::getFormElementStore($x));
        $formService->registerFormElement(ExtraAttributeModel::ATTR_COUNTRY, fn($x) => $websiteBaseFormService::getFormElementCountry($x));
        $formService->registerFormElement(ExtraAttributeModel::ATTR_CURRENCY, fn($x) => $websiteBaseFormService::getFormElementCurrency($x));
        $formService->registerFormElement(ExtraAttributeModel::ATTR_ADDRESS, fn($x) => $websiteBaseFormService::getFormElementAddress($event->form->getOwnerUserId() ?? 0, $x));
        $formService->registerFormElement(ExtraAttributeModel::ATTR_NOTIFICATION_CHANNEL, fn($x) => $websiteBaseFormService::getFormElementNotificationChannel($x));
        $formService->registerFormElement(ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS, fn($x) => $websiteBaseFormService::getFormElementNotificationChannels($event->form->getDataSource(), $x));

        // core config
        $formService->registerFormElement('notification.acl_group.staff', fn($x) => $websiteBaseFormService::getFormElementAclGroup($x));
        $formService->registerFormElement('notification.acl_group.support', fn($x) => $websiteBaseFormService::getFormElementAclGroup($x));
        $formService->registerFormElement('notification.acl_group.admin', fn($x) => $websiteBaseFormService::getFormElementAclGroup($x));
        $formService->registerFormElement('notification.user.sender', fn($x) => $websiteBaseFormService::getFormElementPuppetUser($x));
        $formService->registerFormElement('notification.preferred_channel', fn($x) => $websiteBaseFormService::getFormElementNotificationChannel($x));
    }
}