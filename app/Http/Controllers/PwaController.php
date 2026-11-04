<?php

declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WebsiteBase\app\Services\PwaService;

/**
 * Serves the PWA files. Routes in routes/pwa.php (no session, no CSRF).
 */
class PwaController extends Controller
{
    /**
     * @param  PwaService  $pwaService
     */
    public function __construct(protected PwaService $pwaService)
    {
        // The debugbar would inject its html/js into these responses
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }
    }

    /**
     * /manifest.webmanifest
     *
     * @return JsonResponse
     */
    public function manifest(): JsonResponse
    {
        return response()->json($this->pwaService->manifest(), 200, [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'no-cache',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * /sw.js
     *
     * @return Response
     */
    public function serviceWorker(): Response
    {
        return response($this->pwaService->serviceWorkerScript(), 200, [
            'Content-Type'  => 'application/javascript; charset=utf-8',
            // Always ask the server, so a new version is found on the next update check
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * /offline
     * Fetched once by the service worker while online, then shown from its cache.
     *
     * @return Response
     */
    public function offline(): Response
    {
        return response($this->pwaService->offlineHtml(), 200, [
            'Content-Type'  => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Robots-Tag'  => 'noindex',
        ]);
    }
}
