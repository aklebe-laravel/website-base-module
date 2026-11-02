<?php
declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Services;

use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\CacheService;
use Modules\SystemBase\app\Services\ParserService;
use Modules\WebsiteBase\app\Events\InitNavigation;
use Modules\WebsiteBase\app\Models\Navigation as NavigationModel;
use Modules\WebsiteBase\app\Models\Store;
use Modules\WebsiteBase\app\Models\User;
use Spatie\Navigation\Navigation;
use Spatie\Navigation\Section;
use Throwable;

class Setting
{
    /**
     * Extra attribute of the user: time zone for the times shown to this user (empty = installation).
     */
    public const string USER_ATTRIBUTE_TIMEZONE = 'timezone';

    /**
     * @var Store|null
     */
    protected ?Store $store = null;

    /**
     * @var Navigation|null
     */
    private ?Navigation $navigation = null;

    /**
     * @var ParserService
     */
    private ParserService $parser;

    public function __construct()
    {
        $this->parser = app(ParserService::class);

        // navigation placeholders
        $placeHolders = [
            'user' => [
                'parameters' => [],
                'callback'   => function (array $placeholderParameters, array $parameters, array $recursiveData) {
                    $user = app('website_base_settings')->currentUser();

                    return $user->name;
                },
            ],
        ];
        $this->parser->setPlaceholders($placeHolders);
    }

    /**
     * Get the current store
     *
     * @return ?Store
     */
    public function getStore(): ?Store
    {
        if ($this->store) {
            return $this->store;
        }

        try {
            // @TODO: load current store by site url host
            $this->store = Store::with([])
                ->where('code', 'default')
                ->first();
            if ($this->store) {
                return $this->store;
            }
        } catch (Throwable $exception) {
            Log::error("Error by getting store!", [__METHOD__]);
            Log::error($exception->getMessage());
        }

        $this->store = app(Store::class);

        return $this->store;
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return $this->getStore()
                   ?->getKey() ?? null;
    }

    /**
     * @return User|null
     */
    public function currentUser(): ?User
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);
        $user = $userService->currentUser();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param  mixed  $resources
     *
     * @return bool
     */
    public function currentUserHasAclResource(mixed $resources): bool
    {
        if ($user = $this->currentUser()) {
            return $user->hasAclResource($resources);
        }

        return false;
    }

    /**
     * @return User|null
     * @throws Exception
     */
    public function getSiteOwner(): ?User
    {
        /** @var User $user */
        if (!($user = User::withAclResources(['site_owner'])
            ->first())
        ) {
            throw new Exception('Site Owner not defined. Missing User with resource "site_owner"');
        }

        return $user;
    }

    /**
     * @return Navigation|null
     */
    public function getNavigation(): ?Navigation
    {
        if (!$this->navigation && $this->getStore()) {
            InitNavigation::dispatch();
        }

        return $this->navigation;
    }

    /**
     * @param  Navigation  $navigation
     *
     * @return void
     */
    public function setNavigation(Navigation $navigation): void
    {
        $this->navigation = $navigation;
    }

    /**
     * @param $url
     *
     * @return array
     */
    public function getNavigationSisters($url): array
    {
        return $this->walkGetSisters($url, $this->navigation->tree());
    }

    /**
     * @param $url
     * @param $children
     *
     * @return array|null
     */
    protected function walkGetSisters($url, $children): ?array
    {
        foreach ($children as $child) {

            // Important: First check children, not the parent!
            if ($child['children']) {
                if ($res = $this->walkGetSisters($url, $child['children'])) {
                    return $res;
                }
            }

            if ($child['url'] == $url) {
                return $children;
            }

        }

        return null;
    }

    /**
     * @param  Navigation|Section  $navigation
     * @param  NavigationModel     $navigationModel
     *
     * @return void
     */
    protected function addNavigationItem(Navigation|Section $navigation, NavigationModel $navigationModel): void
    {
        if (!$navigationModel->is_enabled) {
            return;
        }

        if ($navigationModel->route) {
            if (!\Route::has($navigationModel->route)) {
                Log::warning("Missing Route: ", [$navigationModel->route, $navigationModel->route_params]);

                return;
            }
            $url = route($navigationModel->route, $navigationModel->route_params);
        } else {
            $url = $navigationModel->uri;
        }
        if (!$url) {
            $url = ''; // force string
        }

        $addCond = $navigationModel->acl_resources ? $this->currentUserHasAclResource($navigationModel->acl_resources) : true;
        $navigation->addIf($addCond, $navigationModel->label ?? '', $url, function (Section $section) use ($navigationModel) {

            $section->attributes['icon_class'] = $navigationModel->icon_class ? trim($navigationModel->icon_class) : 'check';
            $section->attributes['id'] = 'nav-'.$navigationModel->getKey();
            $section->attributes['code'] = $navigationModel->code;
            // tags like 'CONTROL_DARK_MODE' let the theme render special entries (controls instead of links)
            $section->attributes['tags'] = $navigationModel->tags ?? [];
            $section->title = $this->parser->parse($section->title);

            //// load relation if not already done
            //if (!$navigationModel->relationLoaded('children')) {
            //    $navigationModel = $navigationModel->load('children');
            //}

            if ($navigationModel->relationLoaded('children')) {
                /** @var NavigationModel $child */
                foreach ($navigationModel->children as $child) {
                    $this->addNavigationItem($section, $child);
                }
            }

        });
    }

    /**
     * @return void
     */
    public function createNavigation(): void
    {
        /**
         * Remember navigation is user permission based.
         * Do not cache navigation globally!
         */
        $navigation = app(CacheService::class)->rememberFrontendUserBased('user-navigation', function () {

            /** @var WebsiteService $websiteService */
            $websiteService = app(WebsiteService::class);

            //
            $navigation = Navigation::make();

            if (!$websiteService->isStoreVisibleForUser()) {
                // @todo: create an alternate navigation for not allowed users ...
                $navigation->add(__('Home'), route('home'));
            } else {
                $navigationModelBuilder = NavigationModel::getTree();
                /** @var NavigationModel $navigationModel */
                foreach ($navigationModelBuilder->get() as $navigationModel) {
                    $this->addNavigationItem($navigation, $navigationModel);
                }
            }

            return $navigation;
        });


        $this->setNavigation($navigation);
    }


    // ------------------------------------------------------------------
    // Time zones
    //
    // Internally everything is UTC (app.timezone, database, calculations).
    // Only what people see or type in is local:
    //   installationTimezone(): shared things like the time windows of VM groups (config 'website-base.timezone')
    //   displayTimezone():      times shown to the current user (user setting, otherwise the installation)
    // ------------------------------------------------------------------

    /**
     * Time zone of this installation (env APP_DISPLAY_TIMEZONE), e.g. 'Europe/Berlin'. Default: app.timezone (UTC).
     * For everything that is the same for all users, like schedules entered as local times.
     *
     * @return string
     */
    public function installationTimezone(): string
    {
        $timezone = (string) config('website-base.timezone', '');

        return self::isValidTimezone($timezone) ? $timezone : (string) config('app.timezone', 'UTC');
    }

    /**
     * Time zone for the times shown to the current user: user setting 'timezone', otherwise the installation.
     * Without a user (queue, scheduler) always the installation.
     *
     * @return string
     */
    public function displayTimezone(): string
    {
        $user = $this->currentUser();
        $timezone = $user ? (string) $user->getExtraAttribute(self::USER_ATTRIBUTE_TIMEZONE, '') : '';

        return self::isValidTimezone($timezone) ? $timezone : $this->installationTimezone();
    }

    /**
     * A point in time in the display time zone of the current user, ready to format ('H:i', isoFormat(), ...).
     *
     * @param  int|string|DateTimeInterface  $time  UNIX timestamp, date string (UTC if without zone) or date object
     *
     * @return Carbon
     */
    public function toDisplayTime(int|string|DateTimeInterface $time): Carbon
    {
        $carbon = is_int($time) ? Carbon::createFromTimestamp($time) : Carbon::parse($time);

        return $carbon->setTimezone($this->displayTimezone());
    }

    /**
     * @param  string  $timezone
     *
     * @return bool
     */
    public static function isValidTimezone(string $timezone): bool
    {
        return $timezone !== '' && in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }

    /**
     * Options for a select: identifier => label ('Europe/Berlin' => 'Europe/Berlin (UTC+01:00)').
     *
     * @return array<string, string>
     */
    public static function timezoneOptions(): array
    {
        $options = [];
        foreach (DateTimeZone::listIdentifiers() as $identifier) {
            $options[$identifier] = sprintf('%s (UTC%s)', $identifier, (new \DateTime('now', new DateTimeZone($identifier)))->format('P'));
        }

        return $options;
    }
}
