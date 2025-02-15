<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Http\Controllers\Controller;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Modules\WebsiteBase\Http\Controllers\RouteServiceProvider;

class UserController extends Controller
{
    protected function getUserResponse(string $id)
    {
        $jsonResponse = new JsonViewResponse('OK');
        $users = app(User::class)->with(['crossSellingProducts.mediaItems', 'mediaItems', 'aclGroups.aclResources']);

        if (is_numeric($id)) {
            //$users->whereId($id);
            throw new \Exception('id not allowed');
        } else {
            $users->whereSharedId($id);
        }


        if ($user = $users->first()) {
            $responseData = $users->first()->toArray();
            $jsonResponse->setData($responseData);
        } else {
            $jsonResponse->setErrorMessage(__('User not found.'));
        }

        return $jsonResponse;
    }

    public function get(Request $request, $id)
    {
        $jsonResponse = $this->getUserResponse($id);

        return $jsonResponse->go();
    }

    public function claim(Request $request, $id)
    {
        // Get target user to claim ...
        if (!($user = app(User::class)->with([])->where('shared_id', $id)->first())) {
            // @todo: error
            Log::error('Unable to claim user. shared_id not found.', [__METHOD__]);
            return redirect()->back();
        }

        /** @var UserService $userService */
        $userService = app(UserService::class);

        // Am I admin?
        if (!$userService->hasUserResource(Auth::user(), AclResource::RES_TESTER)) {
            Log::error('Unable to claim user. Permission denied.', [__METHOD__]);
            // @todo: error
            return redirect()->back();
        }

        // remember admin id to save it in the new session below ...
        $adminUserId = auth()->id();

        //FLUSH THE SESSION SO THAT THE NEXT TIME LOGIN IS CALLED IT RUNS THROUGH ALL AUTH PROCEDURES
        session()->flush();

        // Login claiming user ...
        Auth::login($user);

        // remember admin id to reclaim it later ...
        session()->put('admin_user_id', $adminUserId);

        return redirect()->back();
    }

    public function stopClaim()
    {
        if (!($id = session()->get('admin_user_id', 0))) {
            return redirect()->intended();
        }

        $backRoute = session()->get('admin_user_redirect', '');

        //FLUSH THE SESSION SO THAT THE NEXT TIME LOGIN IS CALLED IT RUNS THROUGH ALL AUTH PROCEDURES
        session()->flush();

        //LOGIN AS THE USER
        Auth::loginUsingId($id);

        //IN CASE YOU WANT TO STORE THE ORIGINAL ADMIN USER FOR REVERTING THE SESSION
        session()->remove('admin_user_id');
        session()->remove('admin_user_redirect');

        if ($backRoute) {
            return redirect()->to($backRoute);
        }

        return redirect()->intended();
    }

}
