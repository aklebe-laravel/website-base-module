<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Http\Controllers\Controller;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class SearchController extends Controller
{
    /**
     * @var string
     */
    protected string $contentView = 'website-base::search';

    /**
     * @param  Request  $request
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function find(Request $request): Factory|View|\Illuminate\Foundation\Application|Application
    {
        $searchString = $request->post('search');

        if (strlen($searchString) < 2) {
            $searchString = '';
        }

        $searchStringLike = '%'.$searchString.'%';

        Log::info("Search Request: ", [
            $searchString,
            $searchStringLike,
            __METHOD__,
        ]);

        return view('website-base::page', [
            'title'                => __('Search Results'),
            'searchString'         => $searchString,
            'searchStringLike'     => $searchStringLike,
            'contentView'          => $this->contentView,
            'renderMode'           => BaseDataTable::RENDER_MODE_FRONTEND,
            'livewireTableOptions' => [
                'filterByParentOwner' => true,
            ],
        ]);
    }
}
