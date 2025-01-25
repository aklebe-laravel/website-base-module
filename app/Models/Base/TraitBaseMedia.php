<?php

namespace Modules\WebsiteBase\app\Models\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\WebsiteBase\app\Models\MediaItem;

trait TraitBaseMedia
{
    use TraitBaseModel;

    /**
     *
     */
    const string IMAGE_MAKER = 'MAKER'; // First/Only Image to show in product lists and current user avatar

    /**
     * General boot...() info: Static Setup for this object like events
     * General initialize...() info: executed for every new instance
     *
     * @return void
     */
    public function initializeTraitBaseMedia(): void
    {
        $this->appends[] = 'image_maker';
    }

    /**
     * @return Attribute
     */
    public function imageMaker(): Attribute
    {
        return Attribute::make(get: fn($value, $attributes) => $this->getContentImage(self::IMAGE_MAKER));
    }

    /**
     * @return BelongsToMany
     */
    public function mediaItems(): BelongsToMany
    {
        // maybe overridden class MediaItem ...
        $mediaClassName = get_class(app('media'));

        return $this->belongsToMany($mediaClassName)->withPivot('content_code')->withTimestamps();
    }

    /**
     *
     * @return BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->mediaItems()->images()->withTimestamps();
    }

    /**
     * Overwrite this to get proper images by specific class!
     * Pivot tables can differ by class objects.
     *
     * @param  string  $contentCode
     * @param  bool    $forceAny  If true: Also select nullable pivots but order by pivots exists
     *
     * @return BelongsToMany
     */
    abstract public function getContentImages(string $contentCode = '', bool $forceAny = true): BelongsToMany;

    /**
     * @param          $images
     * @param  string  $contentCode
     * @param  string  $relationTable
     * @param  bool    $forceAny
     *
     * @return BelongsToMany
     */
    protected function prepareContentImagesBuilder($images, string $contentCode = '', string $relationTable = 'media_item_user', bool $forceAny = true): BelongsToMany
    {
        if ($contentCode) {
            $images->where(function (Builder $b) use ($contentCode, $relationTable, $forceAny) {
                // @todo: content_code is pivot column but pivot_content_code is not working
                $b->where($relationTable.'.content_code', '=', $contentCode);
                if ($forceAny) {
                    // also list no marked items
                    // @todo: content_code is pivot column but pivot_content_code is not working
                    $b->orWhereNull($relationTable.'.content_code');
                }
            });
            if ($forceAny) {
                // order by content_code at top to get the proper items by first()
                $images->orderByPivot('content_code', 'desc');
            }
        }

        return $images;
    }


    /**
     * @param  string  $contentCode
     * @param  bool    $forceAny  If true: Select the first available image if $contentCode does not exist.
     *
     * @return MediaItem|null
     */
    public function getContentImage(string $contentCode, bool $forceAny = true): ?MediaItem
    {
        $images = $this->getContentImages($contentCode, $forceAny);
        $img = $images->first();

        return $img;
    }

    /**
     * Save the $contentCode (like 'MAKER') to the specific media item.
     *
     * @param  string  $contentCode
     * @param  int     $mediaModelId
     * @param  bool    $unique true if there can only exist one $contentCode in the whole relationship (like 'MAKER')
     *
     * @return bool
     */
    public function saveContentImage(string $contentCode, int $mediaModelId, $unique = true): bool
    {
        $images = $this->getContentImages()->get();
        // get all images for this object
        foreach ($images as $image) {
            // remove the old maker
            if ($unique && ($image->pivot->content_code === $contentCode)) {
                $image->pivot->update(['content_code' => null]);
            }
            // assign the new maker
            if ($image->getKey() === $mediaModelId) {
                $image->pivot->update(['content_code' => $contentCode]);
            }
        }
        return true;
    }

    /**
     * Returns relations to replicate.
     *
     * @return array
     */
    public function getReplicateRelations(): array
    {
        return ['mediaItems'];
    }
}
