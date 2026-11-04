<?php

/*
|--------------------------------------------------------------------------
| PWA Routes
|--------------------------------------------------------------------------
|
| Loaded by the RouteServiceProvider WITHOUT the "web" middleware group (no session, no CSRF).
| The service worker must be served from the root ("/sw.js"), otherwise its scope would not cover the whole site.
| The routes always exist; PwaService::isEnabled() only decides whether the layout links them.
|
*/

use Illuminate\Support\Facades\Route;
use Modules\WebsiteBase\app\Http\Controllers\PwaController;

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('website-base.pwa.manifest');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('website-base.pwa.service-worker');
Route::get('/offline', [PwaController::class, 'offline'])->name('website-base.pwa.offline');
