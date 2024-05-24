<?php

namespace Modules\WebsiteBase\app\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Http\Controllers\Controller;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Http\Controllers\UserController;
use Modules\WebsiteBase\app\Models\Token;
use Modules\WebsiteBase\Http\Controllers\Auth\RouteServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthenticatedSessionController extends Controller
{
    /**
     * Destroy an authenticated session.
     *
     * @param  Request  $request
     * @return RedirectResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (session()->get('admin_user_id', 0)) {
            return app(UserController::class)->stopClaim();
        }

        /** @var UserService $userService */
        $userService = app(UserService::class);
        $userService->destroyUserSession();

        return redirect(route('login'));
    }

    /**
     * @param  Request  $request
     * @param $token
     * @return RedirectResponse
     */
    public function token(Request $request, $token): RedirectResponse
    {
        Log::info('Token used', [$token]);

        //        /** @var Builder $tokenBuilder */
        $tokenBuilder = Token::with([])->where('token', '=', $token)->where(function (Builder $b1) {
            $b1->whereNull('expires_at');
            $b1->orWhere('expires_at', '>', date(SystemService::dateIsoFormat8601));
        })->whereNotNull('user_id');

        /** @var Collection $tokens */
        if (($tokens = $tokenBuilder->get()) && ($tokens->count() == 1) && ($token = $tokens->first())) {
            /** @var \Modules\WebsiteBase\app\Models\User $user */
            if ($user = app(User::class)->with([])->whereId($token->user_id)->first()) {

                if ($user->canLogin()) {
                    Auth::login($user);
                } else {
                    Log::error(sprintf("User %s (%s) used token, but denied to login.", $user->name, $user->id),
                        [__METHOD__]);
                }

                // @todo: delete expired tokens by cron/queue
            }
        } else {
            Log::error("Token not found or invalid");
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

}
