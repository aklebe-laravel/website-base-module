<?php

namespace Modules\WebsiteBase\app\Listeners;

use Modules\Form\app\Events\InitFormElements as InitFormElementsEvent;
use Modules\Form\app\Services\FormService;
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
        $formService->registerFormElement('store', fn($x) => $websiteBaseFormService::getFormElementStore($x));
        $formService->registerFormElement('country', fn($x) => $websiteBaseFormService::getFormElementCountry($x));
        $formService->registerFormElement('currency', fn($x) => $websiteBaseFormService::getFormElementCurrency($x));
        $formService->registerFormElement('address', fn($x) => $websiteBaseFormService::getFormElementAddress($event->form->getOwnerUserId(), $x));
        $formService->registerFormElement('notification_channel', fn($x) => $websiteBaseFormService::getFormElementNotificationChannel($x));
        $formService->registerFormElement('preferred_notification_channels', fn($x) => $websiteBaseFormService::getFormElementNotificationChannels($event->form->getDataSource(), $x));

        // core config
        $formService->registerFormElement('notification.acl_group.staff', fn($x) => $websiteBaseFormService::getFormElementAclGroup($x));
        $formService->registerFormElement('notification.acl_group.support', fn($x) => $websiteBaseFormService::getFormElementAclGroup($x));
        $formService->registerFormElement('notification.acl_group.admin', fn($x) => $websiteBaseFormService::getFormElementAclGroup($x));
        $formService->registerFormElement('notification.user.sender', fn($x) => $websiteBaseFormService::getFormElementPuppetUser($x));
        $formService->registerFormElement('notification.preferred_channel', fn($x) => $websiteBaseFormService::getFormElementNotificationChannel($x));
    }
}