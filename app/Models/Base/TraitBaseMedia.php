<?php

namespace Modules\WebsiteBase\app\Models\Base;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\WebsiteBase\app\Models\MediaItem;

trait TraitBaseMedia
{
    use TraitBaseModel;

    const IMAGE_MAKER = 'MAKER'; // First/Only Image to show in product lists and current user avatar

    /**
     * Override this instead of declare $appends with all parent declarations.
     *
     * @return array|string[]
     */
    protected function getArrayableAppends()
    {
        return parent::getArrayableAppends() + [
                'image_maker',
            ];
    }

    protected function imageMaker(): Attribute
    {
        return Attribute::make(get: fn($value, $attributes) => $this->getContentImage(self::IMAGE_MAKER),);
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
        return $this->mediaItems()->withTimestamps()->where('media_type', MediaItem::MEDIA_TYPE_IMAGE);
    }

    /**
     * Overwrite this to get proper images by specific class!
     * Pivot tables can differ by class objects.
     *
     * @param  string  $contentCode
     * @param  bool  $forceAny  If true: Also select nullable pivots but order by pivots exists
     *
     * @return BelongsToMany
     */
    public function getContentImages(string $contentCode = '', bool $forceAny = true): BelongsToMany
    {
        $images = $this->images()->withPivot(['content_code']);

        if ($contentCode) {
            // ...
        }

        return $images;
    }

    /**
     * @param  string  $contentCode
     * @param  bool  $forceAny  If true: Select the first available image if $contentCode does not exist.
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
     * Returns relations to replicate.
     *
     * @return array
     */
    public function getReplicateRelations(): array
    {
        return ['mediaItems'];
    }
}
