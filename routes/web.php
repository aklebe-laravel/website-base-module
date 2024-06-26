<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//
use Illuminate\Support\Facades\Route;
use Modules\Acl\app\Http\Middleware\AdminUserPresent;
use Modules\Acl\app\Http\Middleware\StaffUserPresent;
use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Http\Controllers\Auth\AuthenticatedSessionController;
use Modules\WebsiteBase\app\Http\Controllers\CmsPageController;
use Modules\WebsiteBase\app\Http\Controllers\ManageDataController;
use Modules\WebsiteBase\app\Http\Controllers\PreviewNotifyEventController;
use Modules\WebsiteBase\app\Http\Controllers\SearchController;
use Modules\WebsiteBase\app\Http\Controllers\UserController;
use Modules\WebsiteBase\app\Services\WebsiteService;

/** @var WebsiteService $websiteService */
$websiteService = app(WebsiteService::class);
$defaultMiddleware = $websiteService->getDefaultMiddleware();

/**
 * In this group we need admin is logged in.
 */
Route::group(['middleware' => [AdminUserPresent::class]], function () {

    Route::get('/admin-panel/{page}', function ($page) {

        $view = 'admin-panel.'.$page;

        return view('website-base::page', [
            'title'       => __('title.'.$view),
            'contentView' => $view,
        ]);

    })->name('admin-panel');

});

/**
 * In this group we need staff user is logged in.
 */
Route::group(['middleware' => [StaffUserPresent::class]], function () {

    /**
     * Claim a user.
     */
    Route::get('user/claim/{id}', [
        UserController::class,
        'claim'
    ])->name('user.claim');

    /**
     * Preview notify events.
     */
    Route::get('/preview-notify-event/{id}/{userId?}', [
        PreviewNotifyEventController::class,
        'show'
    ])->name('preview-notify-event');

    /**
     *
     */
    Route::get('/manage-data-all/{modelName?}/{modelId?}', [ManageDataController::class, 'all'])
        //    Route::get('/manage-data[/{modelName}[/{modelId}]]', [ManageDataController::class, 'get'])
        ->middleware([
            'auth',
            'verified'
        ])->name('manage-data-all');

});

/**
 * $defaultMiddleware depends on config setting.
 * Store settings can be public or wanted auth and trader are present.
 */
Route::group(['middleware' => $defaultMiddleware], function () {

    Route::get('/', function () {
        return view('content-pages.faq');
    })->name('home');

    // ------------------------------------------------------------------------------
    // Content Pages
    // ------------------------------------------------------------------------------
    Route::get('/content-pages-overview', function () {
        return view('content-pages.overview');
    })->name('content-pages-overview');

    Route::get('/faq', function () {
        return view('content-pages.faq');
    })->name('faq');

    Route::get('/contact', function () {
        return view('content-pages.contact');
    })->name('contact');

    Route::get('/changelog/{filter?}', function ($filter = null) {

        $changeLogCollection = \Modules\WebsiteBase\app\Models\Changelog::with([]);
        // get only entries with public messages
        $changeLogCollection->whereNotNull('messages_public');
        // if staff user, also get staff messages ...
        if (Auth::user()->hasAclResource(AclResource::RES_STAFF)) {
            $changeLogCollection->orWhereNotNull('messages_staff');
        }
        // if 'all' wanted and user is admin or developer, get all messages ...
        if ($filter === 'all' && Auth::user()->hasAclResource([AclResource::RES_STAFF, AclResource::RES_ADMIN])) {
            $changeLogCollection->orWhereNotNull('messages');
        }

        $changeLogCollection->orderByDesc('commit_created_at');

        return view('content-pages.changelog', [
            'changeLogCollection' => $changeLogCollection->get(),
            'filter'              => $filter
        ]);

    })->name('changelog');

    Route::get('/cms/{uri}', [CmsPageController::class, 'get'])->name('cms-page');;

    // ------------------------------------------------------------------------------
    // User specific
    // ------------------------------------------------------------------------------
    Route::get('user/stop-claim', [
        UserController::class,
        'stopClaim'
    ])->name('user.stop-claim');

    Route::get('user/get/{id}', [UserController::class, 'get'])->name('user.get');

    // ------------------------------------------------------------------------------
    // Search
    // ------------------------------------------------------------------------------
    Route::post('/search', [SearchController::class, 'find'])->name('search');

    Route::get('/list/{modelName?}/{modelId?}', [ManageDataController::class, 'showOnly'])->name('list');

    /**
     * possible routes to home:
     * /home
     * /start
     * /dashboard
     */
    Route::get('/{name}', function () {
        return view('content-pages.faq');
    })->where('name', 'home|start|dashboard')->name('home-website');;

});

/**
 * In this group we need auth and trader is logged in.
 */
Route::group([
    'middleware' => [
        'auth',
        'verified'
    ]
], function () {

    Route::get('/manage-data/{modelName?}/{modelId?}', [ManageDataController::class, 'get'])->name('manage-data');

});

/**
 * $defaultMiddleware depends on config setting.
 * Store settings can be public or wanted auth and trader are present.
 */
Route::group(['middleware' => ['auth']], function () {
    Route::get('/user-profile/{id?}', [ManageDataController::class, 'userProfile'])->name('user-profile');
});

/**
 * In this group we don't need any permissions.
 * Same as login, info pages or something like this.
 */
Route::group(['middleware' => []], function () {

    // ------------------------------------------------------------------------------
    // token
    // ------------------------------------------------------------------------------
    Route::get('/token/{id?}', [
        AuthenticatedSessionController::class,
        'token'
    ])->name('token');

    // ------------------------------------------------------------------------------
    // Access denied
    // ------------------------------------------------------------------------------
    Route::get('/access-denied', function () {
        return view('content-pages.access-denied');
    })->name('access-denied');


});


// Auth routes ...
require __DIR__.'/auth.php';