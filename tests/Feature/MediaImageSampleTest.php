<?php

namespace Modules\WebsiteBase\tests\Feature;

use Modules\SystemBase\tests\TestCase;
use Modules\WebsiteBase\app\Models\MediaItem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MediaImageSampleTest extends TestCase
{
    /**
     * Perform assignment new images to media_items where name like 'market-image-%'
     * Images are taken from '/resources/images/samples/products'
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_run_directory_files()
    {
        /** @var MediaItem $mediaItem */
        $mediaItems = MediaItem::with([])->where('name', 'like', 'market-image-%')->get();
        $mediaItemsIterator = $mediaItems->getIterator();

        while ($mediaItemsIterator->valid()) {
            app('system_base_file')->runDirectoryFiles(base_path() . '/resources/images/samples/products', function ($file, $fileInfo) use ($mediaItemsIterator) {

                if (!$mediaItemsIterator->valid()) {
                    return;
                }

                /** @var MediaItem $mediaModel */
                $mediaModel = $mediaItemsIterator->current();
                app('website_base_media')->createMediaFile($mediaModel, $file);
                $mediaItemsIterator->next();
            });
        }
        $this->assertTrue(true);
    }
}
