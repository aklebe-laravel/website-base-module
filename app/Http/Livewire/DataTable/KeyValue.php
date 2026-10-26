<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Support\Collection;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\Base\DefaultDataTable;

class KeyValue extends DefaultDataTable
{
    /**
     * @var array
     */
    public array $keyValueList = [];

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'key',
                'label'      => __('Key'),
                'css_all'    => 'w-50 text-center',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'value',
                'label'      => __('Value'),
                'css_all'    => 'w-50 text-center',
                'searchable' => true,
                'sortable'   => true,
            ],
        ];
    }

    public function getFixCollection(string $collectionName): ?Collection
    {
        return collect($this->keyValueList);
    }

}
