<?php

namespace Modules\WebsiteBase\database\seeders;

use Illuminate\Support\Facades\Log;
use Modules\SystemBase\database\seeders\BaseModelSeeder;
use Modules\WebsiteBase\app\Models\MediaItem;
use Modules\WebsiteBase\app\Models\Store;
use Modules\WebsiteBase\app\Models\User;

class MediaItemSeeder extends BaseModelSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
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
        if ($imagePath = storage_path(config('seeders.users.media_items.image_storage_source_path'))) {
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

            if ($imageFiles) {
                $result = array_merge($result1, $result2);
                foreach ($result as $createdId) {
                    app('website_base_media')->createMediaFile(MediaItem::whereId($createdId)
                                                                        ->first(), $imageFiles[rand(0, (count($imageFiles) - 1))]);
                }
            }

        }

    }
}
