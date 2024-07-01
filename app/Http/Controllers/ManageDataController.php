<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire;
use Modules\Acl\app\Http\Controllers\Controller;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

class ManageDataController extends Controller
{
    /**
     * @todo: should to be configured something else
     * @var array|string[]
     */
    protected array $viewBeforeDataTable = [
        'Deployment' => 'website-base::components.deployment-console',
    ];

    /**
     * @todo: should to be configured something else
     * @var array|string[]
     */
    protected array $viewAfterDataTable = [

    ];

    /**
     * Get data-table - mostly user based items by checking $mapModelAclResources
     * $tableName can be dot seperated to define different table
     * [table].[model]
     * [table].[module].[model] - in this case all livewire classes have to be exists in module folder (untested in details)
     *
     * @param  Request  $request
     * @param  string|null  $modelName
     * @param  mixed|null  $modelId
     * @param  bool  $useCollectionUserFilter
     * @param  bool  $showOnly
     * @return View|Factory|Application
     * @throws Exception
     */
    public function get(Request $request, ?string $modelName = null, mixed $modelId = null,
        bool $useCollectionUserFilter = true, bool $showOnly = false): View|Factory|Application
    {
        if (!$modelName) {
            return view('content-pages.my-shop');
        }

        $currentUser = Auth::user();

        $forceModuleName = '';
        $tableName = $modelName;
        if (str_contains($modelName, '.')) {
            if (($parts = explode('.', $modelName))) {
                if (count($parts) === 2) {
                    $tableName = $parts[0];
                    $modelName = $parts[1];
                } elseif (count($parts) === 3) {
                    $tableName = $parts[0];
                    $forceModuleName = $parts[1];
                    $modelName = $parts[2];
                }
            }
        }

        // form and restrictions
        if ($livewireForm = $showOnly ? null : app('system_base')->findLivewire($modelName, 'livewire-forms',
            $forceModuleName)) {
            /** @var ModelBase $livewireFormClass */
            if ($livewireFormClass = app(Livewire\Mechanisms\ComponentRegistry::class)->getClass($livewireForm)) {
                if ($livewireFormClass::aclResources && !$currentUser->hasAclResource($livewireFormClass::aclResources)) {
                    return view('content-pages.access-denied');
                }
            }
        }

        // datatable and restrictions
        if ($livewireTable = app('system_base')->findLivewire($tableName, 'data-tables', $forceModuleName)) {
            /** @var BaseDataTable $livewireTableClass */
            if ($livewireTableClass = app(Livewire\Mechanisms\ComponentRegistry::class)->getClass($livewireTable)) {
                if ($livewireTableClass::aclResources && !$currentUser->hasAclResource($livewireTableClass::aclResources)) {
                    return view('content-pages.access-denied');
                }
            }
        }

        // @todo: set here depend on model?
        $relevantUserId = Auth::id();

        $contentView = [];
        if (isset($this->viewBeforeDataTable[$modelName])) {
            $contentView[] = $this->viewBeforeDataTable[$modelName];
        }
        if ($showOnly) {
            $contentView[] = 'website-base::components.data-tables.tables.dt-non-edit';
        } else {
            $contentView[] = 'website-base::components.data-tables.tables.split-dt-with-form';
        }
        if (isset($this->viewAfterDataTable[$modelName])) {
            $contentView[] = $this->viewAfterDataTable[$modelName];
        }

        return view('website-base::page', [
            'title'                            => __(($showOnly ? 'Show' : 'Manage')).' '.Str::plural($modelName),
            'contentView'                      => $contentView,
            // modelName is needed for form js x-data="getNewForm('User') ... otherwise console error
            'moduleName'                       => $forceModuleName,
            'modelName'                        => $modelName,
            'livewireForm'                     => $livewireForm,
            'livewireTable'                    => $livewireTable,
            'livewireTableOptions'             => [
                'useCollectionUserFilter' => $useCollectionUserFilter,
            ],
            'formObjectId'                     => $showOnly ? null : $modelId,
            'isFormOpen'                       => !$showOnly && ($modelId !== null),
            'objectModelInstanceDefaultValues' => [
                'user_id' => $relevantUserId,
            ],

        ]);
    }

    /**
     * Get data-table from all (users), butt still checking $mapModelAclResources inside of get()
     *
     * @param  Request  $request
     * @param  string|null  $modelName
     * @param  mixed|null  $modelId
     * @return Application|Factory|View
     * @throws Exception
     */
    public function all(Request $request, ?string $modelName = null, mixed $modelId = null): View|Factory|Application
    {
        return $this->get($request, $modelName, $modelId, false);
    }

    /**
     * @param  Request  $request
     * @param  string|null  $modelName
     * @param  mixed|null  $modelId
     * @return View|Factory|Application
     * @throws Exception
     */
    public function showOnly(Request $request, ?string $modelName = null,
        mixed $modelId = null): View|Factory|Application
    {
        return $this->get($request, $modelName, $modelId, true, true);
    }

    /**
     * @param  Request  $request
     * @param  mixed  $id
     * @return View|Factory|Application
     * @throws Exception
     */
    public function userProfile(Request $request, mixed $id = null): View|Factory|Application
    {
        if ($id === null) {
            $id = Auth::user()->shared_id;
        }
        /** @var User $user */
        if ($user = app(User::class)->with([])->frontendItems()->loadByFrontend($id, 'shared_id')->first()) {
            if ($user->canLogin()) {
                return view('website-base::page', [
                    'title'                            => __('User Profile'),
                    'contentView'                      => 'components.user-profile',
                    'modelName'                        => 'User',
                    'formObjectId'                     => $user->getKey(),
                    'livewireForm'                     => 'website-base::form.user-profile',
                    'objectModelInstanceDefaultValues' => [
                        'user_id' => $user->getKey(),
                    ],
                ]);
            }
        }

        // @todo: 404 User not found, or puppet, or invalid (disabled/deleted/...)
        throw new Exception('User not found, puppet, or invalid (disabled/deleted/...)');
    }

}
