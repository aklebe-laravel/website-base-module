@php
    /**
     * @var BaseDataTable $this
     * @var MediaItem $item
     * @var string $name
     * @var mixed $value
     **/

    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    use Modules\WebsiteBase\app\Models\MediaItem;
    use Modules\WebsiteBase\app\Services\MediaService;

    $filepath = app(MediaService::class)->getMediaItemPath($item);
    $pathInfo = is_file($filepath) ? pathinfo($filepath) : [];
@endphp
@if($pathInfo)
    <div class="bg-secondary-subtle text-secondary">
        <div>File: {{ $pathInfo['extension'] }}</div>
        <div>
            <span class="badge rounded-pill bg-success-subtle text-success">
                {{ app('system_base')->bytesToHuman(filesize($filepath)) }}
            </span>
        </div>
    </div>
@else
    <div class="bg-warning-subtle text-warning text-muted">
        (no file)
    </div>
@endif

