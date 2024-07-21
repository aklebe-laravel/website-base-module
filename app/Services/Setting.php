<?php

namespace Modules\WebsiteBase\app\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\WebsiteBase\app\Events\InitNavigation;
use Modules\WebsiteBase\app\Models\Navigation as NavigationModel;
use Modules\WebsiteBase\app\Models\Store;
use Modules\WebsiteBase\app\Models\User;
use Spatie\Navigation\Navigation;
use Spatie\Navigation\Section;

class Setting
{
    /**
     * @var Store|null
     */
    private ?Store $store = null;

    /**
     * @var Navigation|null
     */
    private ?Navigation $navigation = null;

    /**
     * Get the current store
     *
     * @return ?Store
     */
    public function getStore(): ?Store
    {
        if (!$this->store) {
            try {
                // @TODO: load current store by site url host
                $stores = Store::with([])->where('code', 'default')->get();
                $this->store = $stores ? $stores->first() : null;
            } catch (Exception $ex) {
                Log::error("Error by getting store!", [__METHOD__]);
                Log::error($ex->getMessage());
                $this->store = Store::make(['id' => 0]);
            }
        }

        return $this->store;
    }

    /**
     * @return User|null
     */
    public function currentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * @param  mixed  $resources
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
        if (!($user = User::withAclResources(['site_owner'])->first())) {
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
     * @return void
     */
    public function setNavigation(Navigation $navigation): void
    {
        $this->navigation = $navigation;
    }

    /**
     * @param $url
     * @return array
     */
    public function getNavigationSisters($url): array
    {
        return $this->walkGetSisters($url, $this->navigation->tree());
    }

    /**
     * @param $url
     * @param $children
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
     * @param  NavigationModel  $navigationModel
     * @return void
     */
    protected function addNavigationItem(Navigation|Section $navigation, NavigationModel $navigationModel): void
    {
        if ($navigationModel->route) {
            $url = route($navigationModel->route, $navigationModel->route_params);
        } else {
            $url = $navigationModel->uri;
        }
        if (!$url) {
            $url = ''; // force string
        }

        $addCond = $navigationModel->acl_resources ? $this->currentUserHasAclResource($navigationModel->acl_resources) : true;
        $navigation->addIf($addCond, $navigationModel->label, $url, function (Section $section) use ($navigationModel) {

            $section->attributes['icon_class'] = $navigationModel->icon_class ? trim($navigationModel->icon_class) : 'bi bi-circle';
            $section->attributes['id'] = 'nav-'.$navigationModel->getKey();

            /** @var NavigationModel $child */
            foreach ($navigationModel->children as $child) {
                $this->addNavigationItem($section, $child);
            }

        });
    }

    /**
     * @return void
     */
    public function createNavigation(): void
    {
        //
        // @todo: move sql queries and cache them

        /** @var WebsiteService $websiteService */
        $websiteService = app(WebsiteService::class);

        //
        $navigation = Navigation::make();

        if (!$websiteService->isStoreVisibleForUser()) {
            // @todo: create an alternate navigation for not allowed users ...
            $navigation->add(__('Home'), route('home'));
        } else {
            $navigationModelBuilder = NavigationModel::getRootCategories();
            /** @var NavigationModel $navigationModel */
            foreach ($navigationModelBuilder->get() as $navigationModel) {
                $this->addNavigationItem($navigation, $navigationModel);
            }
        }

        $this->setNavigation($navigation);
    }

}
