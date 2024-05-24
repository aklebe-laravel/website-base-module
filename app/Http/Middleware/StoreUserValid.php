<?php

namespace Modules\WebsiteBase\app\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\WebsiteBase\app\Services\WebsiteService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StoreUserValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     *
     * @return Response|RedirectResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var WebsiteService $websiteService */
        $websiteService = app(WebsiteService::class);
        if ($websiteService->isStoreVisibleForUser()) {
            return $next($request);
        }

        // access not allowed
        return response(view('content-pages.access-denied'));
    }
}
