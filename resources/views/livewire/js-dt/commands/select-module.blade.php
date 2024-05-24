@php
    use Modules\SystemBase\app\Services\ModuleService;use Modules\WebsiteBase\app\Http\Livewire\DataTable\Changelog;

    /** @var \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable $this */
    /** @var string $collectionName */

    /** @var ModuleService $moduleService */
    $moduleService = app(ModuleService::class);

    $options = [
        Changelog::FILTER_ALL => Changelog::FILTER_ALL,
        Changelog::FILTER_APP_ONLY => Changelog::FILTER_APP_ONLY,
    ];
    $moduleService->runOrderedEnabledModules(function(?Nwidart\Modules\Module $module) use (&$options) {
        $options[$module->getStudlyName()] = $module->getName();
        return true;
    });

@endphp
<select wire:model="filters.{{ $collectionName }}.changelog_method"
        class="form-control {{ $this->isFilterDefault($collectionName, 'changelog_method') ? '' : 'bg-warning-subtle' }}">
    @foreach($options as $k => $v)
        <option value="{{ $k }}">{{ $v }}</option>
    @endforeach
</select>
