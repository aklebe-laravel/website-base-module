<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Modules\WebsiteBase\app\Models\User as UserModel;

class UserSearch extends User
{
    /**
     * @var string
     */
    public string $eloquentModelName = UserModel::class;

    /**
     * @var string
     */
    public string $searchStringLike = '';

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('name', 'asc');
    }

    /**
     * The base builder before all filter manipulations.
     * Usually used for all collections (default, selected, unselected), but can be overwritten.
     *
     * @param  string  $collectionName
     *
     * @return Builder|null
     * @throws Exception
     */
    public function getBaseBuilder(string $collectionName): ?Builder
    {
        // $builder = app(\App\Models\User::class)->getBuilderFrontendItems();
        $builder = parent::getBaseBuilder($collectionName);

        if ($this->searchStringLike) {
            $builder->where(function (Builder $b) {
                $b->where('name', 'like', $this->searchStringLike);
                $b->orWhere('shared_id', 'like', $this->searchStringLike);
            });
        }

        return $builder;
    }

}
