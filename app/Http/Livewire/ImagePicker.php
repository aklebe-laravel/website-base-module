<?php

declare(strict_types=1);

namespace Modules\WebsiteBase\app\Http\Livewire;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Illuminate\View\View;

class ImagePicker extends Component
{
    /** @var array<int, array<string, string>> */
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
     * @param $id
     *
     * @return array
     */
    public function getImage($id) : array
    {
        return array_find($this->images, function ($image) use ($id) {
            return $image['id'] === $id;
        });
    }
}
