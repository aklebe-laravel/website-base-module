<?php

namespace Modules\WebsiteBase\database\seeders;

use Illuminate\Support\Facades\Log;
use Modules\SystemBase\database\seeders\BaseModelSeeder;
use Modules\WebsiteBase\app\Models\MediaItem;
use Modules\WebsiteBase\app\Models\Store;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\MediaService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MediaItemSeeder extends BaseModelSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(): void
    {
        parent::run();

        /** @var Store $store */
        if (!($store = Store::with([])
            ->get()
            ->first())
        ) {
            Log::error("No store found", [__METHOD__]);

            return;
        }

        // find seeder start
        if (!($seederStarted = config('seeder_started'))) {
            Log::error("No seeder start found.", [__METHOD__]);

            return;
        }

        $imageFiles = [];
        // if there is a valid path configured and images exists, we create the media images ...
        $storagePath = config('seeders.users.media_items.image_storage_source_path');
        if ($imagePath = storage_path($storagePath)) {
            if (is_dir($imagePath)) {
                if ($scanDirList = scandir($imagePath)) {
                    foreach ($scanDirList as $file) {
                        $fullPath = $imagePath.'/'.$file;
                        if (is_file($fullPath)) {
                            // @todo: check image type?
                            $imageFiles[] = $fullPath;
                        }
                    }
                }
            }
        }

        // get all new generated users
        $userIds = User::with([])
            ->where('created_at', '>=', $seederStarted)
            ->pluck('id');
        foreach ($userIds as $userId) {

            // product images
            $result1 = $this->TryCreateFactories(MediaItem::class, config('seeders.users.media_items.count_product_images', 2), fn() => [
                'store_id'    => $store->id,
                'user_id'     => $userId,
                'media_type'  => MediaItem::MEDIA_TYPE_IMAGE,
                'object_type' => MediaItem::OBJECT_TYPE_PRODUCT_IMAGE,
                'description' => 'created by seeder',
            ]);

            // user images
            $result2 = $this->TryCreateFactories(MediaItem::class, config('seeders.users.media_items.count_avatar_images', 2), fn() => [
                'store_id'    => $store->id,
                'user_id'     => $userId,
                'media_type'  => MediaItem::MEDIA_TYPE_IMAGE,
                'object_type' => MediaItem::OBJECT_TYPE_USER_AVATAR,
                'description' => 'created by seeder',
            ]);

            // from here create or link existing images ...
            if (!$imageFiles) {
                Log::error("No media images found. Check your config 'seeders.users.media_items.image_storage_source_path'. Path: '$storagePath'", [__METHOD__]);

                return;
            }

            /** @var MediaService $mediaService */
            $mediaService = app('website_base_media');

            $result = array_merge($result1, $result2);
            foreach ($result as $createdId) {
                /** @var MediaItem $mediaItem */
                if ($mediaItem = MediaItem::whereId($createdId)->first()) {
                    $imageFile = $imageFiles[rand(0, (count($imageFiles) - 1))];
                    //$mediaItem->extern_url = $imageFile;

                    // check same user has same origin image to avoid generate duplicates and waste disk space
                    /** @var MediaItem $mediaItemFound */
                    if ($mediaItemFound = $mediaService->findUserImageByOrigin($userId, $imageFile)) {
                        // link to existing media item ...
                        $mediaItem->file_name = $mediaItemFound->file_name;
                        $mediaItem->relative_path = $mediaItemFound->relative_path;
                        $mediaItem->extern_url = $imageFile;
                        $mediaItem->save();
                    } else {
                        // create image files ...
                        $mediaService->createMediaFile($mediaItem, $imageFile);
                    }
                }
            }

        }

    }
}
