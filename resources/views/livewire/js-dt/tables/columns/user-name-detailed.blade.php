@php
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;use Modules\WebsiteBase\app\Models\User;

    /**
     * @var BaseDataTable $this
     * @var User|mixed $item
     * @var string $name
     * @var User|mixed $value
     * @var array $column
     * @var array $options
     **/

    if ($user = ($item instanceof User) ? $item : (($value instanceof User) ? $value : null)) {
        $aclGroupStr = implode(', ', $user->aclGroups->pluck('name')->toArray());
        if ($aclGroupStr) {
            data_set($column, 'options.popups.0.title', __('Acl Groups'));
            // data_set($column, 'options.popups.0.label', __('Acl Groups'));
            data_set($column, 'options.popups.0.content', $aclGroupStr);
        }

        if (!is_scalar($value)) {
            $value = $user->name;
        }
    }
@endphp
@include("data-table::livewire.js-dt.tables.columns.default")
