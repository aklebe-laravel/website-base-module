<?php

declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

/**
 * Image selection of the ImagePicker as cards: all public images plus the own ones of the current user
 * (own first), searchable and sortable by name or last update.
 *
 * A click on a card dispatches the browser event 'media-item-picked' ({ id, picker }) on window,
 * the ImagePicker with the id $pickerId handles it (window, because a modal may teleport its content).
 */
class MediaItemPicker extends MediaItem
{
    /**
     * Currently selected media item (highlighted), given by the ImagePicker.
     *
     * @var int|null
     */
    #[Reactive]
    public ?int $pickedId = null;

    /**
     * Livewire id of the ImagePicker that receives the picked id.
     *
     * @var string
     */
    #[Locked]
    public string $pickerId = '';

    #[Locked]
    public string $tableView = self::TABLE_VIEW_CARDS;

    #[Locked]
    public string $cardView = 'website-base::components.data-tables.cards.image-picker';

    #[Locked]
    public string $cardsGridCss = 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4';

    public bool $editable = false;

    public bool $removable = false;

    public bool $canAddRow = false;

    public bool $hasCommands = false;

    /**
     * Visibility is decided by MediaItemModel::scopeVisibleImagesFor(), not by the owner column.
     *
     * @var bool
     */
    public bool $filterByParentOwner = false;

    /**
     * @var array|true[]
     */
    public array $enabledCollectionNames = [
        self::COLLECTION_NAME_DEFAULT => true,
    ];

    /**
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('name', 'asc');
    }

    /**
     * Search and rows only: no media/object type filters, no settings menu (new entry, export ...).
     *
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        $this->removeFilterElement('filter_media_type');
        $this->removeFilterElement('filter_object_type');

        $this->filterElementConfig['search']['view'] = 'data-table::livewire.js-dt.filters.default-elements.text';
        $this->filterElementConfig['rows_per_page']['default'] = 24;
        $this->filterElementConfig['rows_per_page']['options'] = [
            12 => 12,
            24 => 24,
            48 => 48,
            96 => 96,
        ];
    }

    /**
     * Public images and the own ones, own images first (before the chosen sort).
     *
     * @param  string|null  $collectionName
     *
     * @return Builder|null
     * @throws \Exception
     */
    public function getBaseBuilder(?string $collectionName = null): ?Builder
    {
        $userId = Auth::id();

        $builder = parent::getBaseBuilder($collectionName)
            ->visibleImagesFor($userId);

        if ($userId) {
            $builder->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId]);
        }

        return $builder;
    }

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'    => 'id',
                'label'   => 'ID',
                'visible' => false,
            ],
            [
                'name'       => 'name',
                'label'      => __('Name'),
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'visible'    => false,
                'searchable' => true,
            ],
            [
                'name'     => 'updated_at',
                'label'    => __('Updated At'),
                'sortable' => true,
            ],
        ];
    }
}
