<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\app\Models\AclGroup;
use Modules\Acl\app\Models\AclResource;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\CacheService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Address;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\Country;
use Modules\WebsiteBase\app\Models\Currency;
use Modules\WebsiteBase\app\Models\Store;
use Modules\WebsiteBase\app\Models\User;

class WebsiteBaseFormService extends BaseService
{
    /**
     * @return array
     */
    public static function getFormElementYesOrNoOptions(): array
    {
        return [1 => __('Yes'), 0 => __('No')];
    }

    /**
     * @return array
     */
    public static function getFormElementStoreOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_store.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            return $systemService->toHtmlSelectOptions(Store::orderBy('code', 'ASC')->get(),
                fn($x) => sprintf("%s (%s)", $x->code, $x->getKey()),
                'id',
                $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementStore(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementStoreOptions(),
            'label'        => __('Store'),
            'description'  => __('Store'),
            'validator'    => [
                'nullable',
                'integer',
            ],
            'css_group'    => 'col-12 col-md-4',
        ], $mergeData);
    }

    /**
     * @return array
     */
    public static function getFormElementCountryOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_country.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            return $systemService->toHtmlSelectOptions(Country::orderBy('nice_name', 'ASC')->selectRaw('*, LOWER(iso) as iso')->get(), ['nice_name', 'iso'], 'iso', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementCountry(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementCountryOptions(),
            'cmpCi'        => true,
            'label'        => __('Country'),
            'description'  => __('Country'),
            'validator'    => [
                'nullable',
                'string',
                'Max:10',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }

    /**
     * @return array
     */
    public static function getFormElementCurrencyOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_currency.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            return $systemService->toHtmlSelectOptions(Currency::orderBy('name', 'ASC')->get(), fn($v) => sprintf("%s (%s)", $v->name, $v->code), 'code', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementCurrency(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementCurrencyOptions(),
            'cmpCi'        => true,
            'label'        => __('Currency'),
            'description'  => __('Currency'),
            'validator'    => [
                'nullable',
                'string',
                'Max:10',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }

    /**
     * @return array
     */
    public static function getFormElementNotificationChannelOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_notification_channel.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');
            $registeredChannels = $systemService->assignArrayKeysByValue(app(SendNotificationService::class)->getRegisteredChannelNames());

            return $systemService->toHtmlSelectOptions($registeredChannels, first: $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementNotificationChannel(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementNotificationChannelOptions(),
            'label'        => __('Notification Channel'),
            'description'  => __('Notification Channel Description'),
            'validator'    => [
                'nullable',
                'string',
                'Max:255',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }

    /**
     * @param  Model|TraitAttributeAssignment  $object
     *
     * @return array
     */
    public static function getFormElementNotificationChannelsOptions(mixed $object): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_notification_channels.options', function () use ($object) {
            $systemService = app('system_base');
            $sortedChannels = $object->getExtraAttribute(ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS, []) ?? [];
            $registeredChannels = app(SendNotificationService::class)->getRegisteredChannelNames();
            $sortedChannels = array_merge($sortedChannels, $registeredChannels);

            return $systemService->toHtmlSelectOptions($sortedChannels);
        });
    }

    /**
     * @param  Model|TraitAttributeAssignment  $object
     * @param  array                           $mergeData
     *
     * @return array
     */
    public static function getFormElementNotificationChannels(mixed $object, array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'sortable_multi_select',
            'options'      => static::getFormElementNotificationChannelsOptions($object),
            'label'        => __('Preferred Notification Channels'),
            'description'  => __('Preferred Notification Channels Description'),
            'validator'    => [
                'nullable',
                'array',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }

    /**
     * @return array
     */
    public static function getFormElementAclGroupOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_acl_group.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            return $systemService->toHtmlSelectOptions(app(AclGroup::class)->with([])->orderBy('name')->get(), 'name', 'id', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementAclGroup(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementAclGroupOptions(),
            'label'        => __('Acl Group'),
            'description'  => __('Acl Group'),
            'validator'    => [
                'nullable',
                'integer',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }


    /**
     * @param  int  $userId
     *
     * @return array
     */
    public static function getFormElementAddressOptions(int $userId): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_address.options', function () use ($userId) {
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            $list = [];

            if ($userId) {
                $collection = Address::with([])->where('user_id', $userId)->orderBy('lastname', 'ASC')->get();

                foreach ($collection as $item) {
                    $list[] = [
                        'id'    => $item->id,
                        'label' => sprintf("%s %s, %s %s, %s",
                            $item->firstname,
                            $item->lastname,
                            $item->zip,
                            $item->city,
                            $item->street),
                    ];
                }
            }

            return $systemService->toHtmlSelectOptions($list, ['label'], 'id', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  int    $userId
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementAddress(int $userId, array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementAddressOptions($userId),
            'label'        => __('Address'),
            'description'  => __('Address'),
            'validator'    => [
                'nullable',
                'integer',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }

    /**
     * @return array
     */
    public static function getFormElementPuppetUserOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_puppet_user.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            return $systemService->toHtmlSelectOptions(User::withAclResources([AclResource::RES_NON_HUMAN])->orderBy('name')->get(), ['id', 'name'], 'id', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementPuppetUser(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementPuppetUserOptions(),
            'label'        => __('Puppet'),
            'description'  => __('Puppet user - no human'),
            'validator'    => [
                'nullable',
                'integer',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }

}