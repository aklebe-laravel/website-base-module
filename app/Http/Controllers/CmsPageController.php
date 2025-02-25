<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Modules\Acl\app\Http\Controllers\Controller;
use Modules\WebsiteBase\app\Services\CmsService;

class CmsPageController extends Controller
{
    /**
     * Get data-table - mostly user based items by checking $mapModelAclResources
     * $tableName can be dot seperated to define different table
     * [table].[model]
     * [table].[module].[model] - in this case all livewire classes have to be exists in module folder (untested in details)
     *
     * @param  Request  $request
     * @param  string  $uri
     * @return Application|Factory|View
     */
    public function get(Request $request, string $uri): View|Factory|Application
    {
        /** @var CmsService $cmsService */
        $cmsService = app(CmsService::class);

        if (($page = $cmsService->getRoutePage($uri)) && ($page->is_enabled)) {
            $title = $cmsService->getCalculated($page, $page->title);
            $content = $cmsService->getCalculated($page, $page->content);

        } else {
            $title = "Not found";
            $content = "";
        }

        return view('content-pages.cms-page', [
            'cmsPage' => $page,
            'title'   => $title,
            'content' => $content,
        ]);
    }

}
