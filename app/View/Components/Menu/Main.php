<?php

namespace Modules\WebsiteBase\app\View\Components\Menu;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Main extends Component
{
    public array $navTree;
    public string $iconAssetPath;

    /**
     *
     */
    public function __construct()
    {
        $this->iconAssetPath = theme_asset('images/no_image_available.jpg');
        $this->navTree = app('website_base_settings')->getNavigation()->tree();
    }

    /**
     * @return View|Factory|Htmlable|Closure|string|\Illuminate\View\View
     */
    public function render(): View|Factory|Htmlable|Closure|string|\Illuminate\View\View
    {
        return view('components.menu.main', ['component' => $this]);
    }

    /**
     * Check if any child item is active.
     */
    public static function hasActiveDescendant(object|array $item): bool
    {
        $children = collect(((object)$item)->children ?? []);

        return $children->some(function ($child) {
            $childObj = (object)$child;
            return ($childObj->active ?? false) || self::hasActiveDescendant($childObj);
        });
    }

    public static function isItemActive(object|array $item): bool
    {
        $itemObj = (object)$item;
        // Check item itself OR any child
        return ($itemObj->active ?? false) || self::hasActiveDescendant($itemObj);
    }
}