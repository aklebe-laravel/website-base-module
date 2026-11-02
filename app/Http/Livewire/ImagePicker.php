<?php

declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Modules\WebsiteBase\app\Models\MediaItem;

/**
 * Picks an image (media item) for a form field. The selection is the data table
 * 'website-base::data-table.media-item-picker' as cards (public images plus the own ones).
 */
class ImagePicker extends Component
{
    // This binds the property directly to wire:model of the parent component
    #[Modelable]
    public ?int $selectedImageId = null;

    /**
     * Called by the browser event 'media-item-picked' of a card or by the remove button.
     *
     * @param  int|null  $id
     *
     * @return void
     */
    public function selectImage(?int $id): void
    {
        // only images the user may pick (or none)
        if ($id !== null
            && !$this->visibleImagesQuery()
                ->whereKey($id)
                ->exists()
        ) {
            return;
        }

        $this->selectedImageId = $id;
    }

    /**
     * The selected image, independent of the selection: it stays visible even if it is not
     * (or no longer) pickable, like a private image of another user.
     *
     * @return array{label: string, image: string}|null
     */
    #[Computed]
    public function selectedImage(): ?array
    {
        if ($this->selectedImageId === null || !($item = MediaItem::find($this->selectedImageId))) {
            return null;
        }

        return [
            'label' => (string) $item->name,
            'image' => $item->final_thumb_medium_url,
        ];
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('website-base::livewire.image-picker');
    }

    /**
     * @return Builder
     */
    protected function visibleImagesQuery(): Builder
    {
        return app(MediaItem::class)
            ->newQuery()
            ->visibleImagesFor(Auth::id());
    }
}
