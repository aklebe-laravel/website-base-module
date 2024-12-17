<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\On;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\WebsiteBase\app\Models\User as UserModel;

class User extends BaseDataTable
{
    use BaseWebsiteBaseDataTable;

    /**
     * @var string
     */
    public string $eloquentModelName = UserModel::class;

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('updated_at', 'desc');
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but before any update methods are called
     *
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        if ($this->canManage()) {
            $this->rowCommands = [
                'claim_user' => 'website-base::livewire.js-dt.tables.columns.buttons.claim-user',
                ...$this->rowCommands,
            ];
        } else {
            $this->rowCommands = [];
        }
    }

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'id',
                'label'      => __('ID'),
                'format'     => 'number',
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-lg text-muted font-monospace text-end w-5',
            ],
            [
                'name'     => 'is_enabled',
                'label'    => __('Enabled'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'hide-mobile-show-lg text-center w-5',
                'sortable' => true,
                'icon'     => 'check',
            ],
            [
                'name'     => 'is_deleted',
                'label'    => __('Deleted'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'hide-mobile-show-lg text-center w-5',
                'sortable' => true,
                'icon'     => 'x',
            ],
            [
                'name'       => 'last_visited_at',
                'label'      => __('Online'),
                'css_all'    => 'hide-mobile-show-lg text-center w-5',
                'view'       => 'data-table::livewire.js-dt.tables.columns.online',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'globe',
            ],
            [
                'name'    => 'id',
                'label'   => __('Link'),
                'format'  => 'number',
                'css_all' => 'hide-mobile-show-sm text-center w-5',
                'view'    => 'data-table::livewire.js-dt.tables.columns.user',
                'icon'    => 'link',
            ],
            [
                'name'       => 'name',
                'label'      => __('Name'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'w-50',
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 30,
                ],
                'view'       => 'website-base::livewire.js-dt.tables.columns.user-name-detailed',
                'icon'       => 'person',
            ],
            [
                'name'       => 'email',
                'label'      => __('Email'),
                'searchable' => true,
                'sortable'   => true,
                'visible'    => false, // !$this->useCollectionUserFilter,
                'css_all'    => 'hide-mobile-show-lg w-20',
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 25,
                ],
                'icon'       => 'envelope',
            ],
            [
                'name'       => 'extra_attributes.user_bio',
                'label'      => __('Bio'),
                'searchable' => false,
                'sortable'   => false,
                'visible'    => false,
                'css_all'    => 'w-20',
                'icon'       => 'card-text',
            ],
            [
                'name'       => 'updated_at',
                'label'      => __('Updated At'),
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-sm w-10',
                'icon'       => 'arrow-clockwise',
            ],
            [
                'name'       => 'shared_id',
                'searchable' => true,
                'visible'    => false,
            ],
        ];
    }

    /**
     * @param  mixed        $livewireId
     * @param  mixed        $itemId
     * @param  string|null  $currentUrl
     *
     * @return bool
     * @todo: duplicate code UserController::claim()
     */
    #[On('claim')]
    public function claim(mixed $livewireId, mixed $itemId, string $currentUrl = null): bool
    {
        Log::error('Claiming: ', [$livewireId, $itemId, __METHOD__]);
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }


        // Get target user to claim ...
        if (!($user = app(\App\Models\User::class)->with([])->where('shared_id', $itemId)->first())) {
            // @todo: error
            Log::error('id not found.', [__METHOD__]);

            return false;
        }

        /** @var UserService $userService */
        $userService = app(UserService::class);

        // Am I admin?
        if (!$userService->hasUserResource(Auth::user(), AclResource::RES_MANAGE_USERS)) {
            // @todo: error
            Log::error('resource not found.', [__METHOD__]);

            return false;
        }

        // remember admin id to save it in the new session below ...
        $adminUserId = auth()->id();

        //FLUSH THE SESSION SO THAT THE NEXT TIME LOGIN IS CALLED IT RUNS THROUGH ALL AUTH PROCEDURES
        session()->flush();

        // Login claiming user ...
        Auth::login($user);

        $route = route('manage-data', [
            'modelName' => 'User',
            'modelId'   => $user->getKey(),
        ]);

        // remember admin id to reclaim it later ...
        session()->put('admin_user_id', $adminUserId);
        session()->put('admin_user_redirect', $currentUrl ?: $route);

        Redirect::to($route);

        return true;
    }

    /**
     * The base builder before all filter manipulations.
     * Usually used for all collections (default, selected, unselected), but can overwritten.
     *
     * @param  string  $collectionName
     *
     * @return Builder|null
     * @throws Exception
     */
    public function getBaseBuilder(string $collectionName): ?Builder
    {
        $builder = parent::getBaseBuilder($collectionName);
        if ($this->useCollectionUserFilter) {
            $builder = $builder->frontendItems();
        }

        return $builder;
    }

    /**
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     *
     * @return bool
     * @throws Exception
     */
    #[On('delete-item')]
    public function deleteItem(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var UserModel $user */
        if ($user = app(\App\Models\User::class)->with([])->find($itemId)) {
            $result = $user->deleteIn3Steps();
            if ($result['success']) {
                $this->addSuccessMessage($result['message']);
            } else {
                $this->addErrorMessages($result['message']);
            }
            $this->closeForm();
        }

        return true;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isItemValid($item): bool
    {
        /** @var UserModel $item */
        return ($item->is_enabled && !$item->is_deleted && ($item->order_to_delete_at === null));
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isItemWarn($item): bool
    {
        /** @var UserModel $item */
        return ((!$item->email) || (!$item->name) || (!$item->shared_id) || $item->hasAclResource('puppet', []));
    }

}
