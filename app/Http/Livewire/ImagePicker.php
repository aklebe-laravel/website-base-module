<?php

declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Http\Livewire;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Illuminate\View\View;

class ImagePicker extends Component
{
    /** @var array<int, array{id: int, label: string, image: string}> */
    public array $images = [];

    // This binds the property directly to wire:model of the parent component
    #[Modelable]
    public ?int $selectedImageId = null;

    /**
     * @param  int|null  $id
     *
     * @return void
     */
    public function selectImage(?int $id): void
    {
        $this->selectedImageId = $id;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('website-base::livewire.image-picker');
    }

    /**
     * The image entry of $id, null if there is none: no selection, or the selected
     * media item is not part of $images (deleted, or excluded by the options scope).
     *
     * @param  int|null  $id
     *
     * @return array|null
     */
    public function getImage(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        return array_find($this->images, fn(array $image) => (int) $image['id'] === $id);
    }
}
