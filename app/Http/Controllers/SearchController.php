<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
     * @return RedirectResponse
     */
    public function find(Request $request): RedirectResponse
    {
        if (($searchString = $request->post('search')) !== null) {
            if (strlen($searchString) < 2) {
                $searchString = '';
            }

            $searchStringLike = '%'.$searchString.'%';

            Log::info("Search Request: ", [
                $searchString,
                $searchStringLike,
                __METHOD__,
            ]);

            session()->put('searchString', $searchString);
            session()->put('searchStringLike', $searchStringLike);
        }

        return redirect()->route('search-results');
    }

    /**
     * @param  Request  $request
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function searchResults(Request $request): Factory|View|\Illuminate\Foundation\Application|Application
    {
        $searchString = session('searchString');
        $searchStringLike = session('searchStringLike');

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
