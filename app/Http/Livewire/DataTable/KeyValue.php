<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class KeyValue extends BaseDataTable
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

    public function getFixCollection(string $collectionName): ?\Illuminate\Support\Collection
    {
        return collect($this->keyValueList);
    }

}
