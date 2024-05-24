<?php

namespace Modules\WebsiteBase\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\WebsiteBase\app\Models\MediaItem;
use Modules\WebsiteBase\app\Models\Store;

class MediaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var User $user */
        /** @var Store $store */
        $store = Store::with([])->get()->first();

        //
        $users = User::with(['aclGroups.aclResources'])->whereHas('aclGroups.aclResources', function ($query) {
            return $query->where('code', '=', 'puppet');
        })->get();
        // product images
        foreach ($users as $user) {
            MediaItem::factory()
                     ->count(3)
                     ->create([
                         'store_id'    => $store->id,
                         'user_id'     => $user->id ?? null,
                         'media_type'  => MediaItem::MEDIA_TYPE_IMAGE,
                         'object_type' => MediaItem::OBJECT_TYPE_PRODUCT_IMAGE,
                         'description' => 'created by seeder',
                     ]);

            // user images
            MediaItem::factory()
                     ->count(2)
                     ->create([
                         'store_id'    => $store->id,
                         'user_id'     => $user->id ?? null, // @todo: ???
                         'media_type'  => MediaItem::MEDIA_TYPE_IMAGE,
                         'object_type' => MediaItem::OBJECT_TYPE_USER_AVATAR,
                         'description' => 'created by seeder',
                     ]);

        }

        $users = User::with(['aclGroups.aclResources'])->whereHas('aclGroups.aclResources', function ($query) {
            return $query->where('code', '=', 'admin');
        })->get();
        $user = $users->first();
        // category images
        MediaItem::factory()
                 ->count(30)
                 ->create([
                     'store_id'    => $store->id,
                     'user_id'     => $user->id ?? null,
                     'media_type'  => MediaItem::MEDIA_TYPE_IMAGE,
                     'object_type' => MediaItem::OBJECT_TYPE_CATEGORY_IMAGE,
                     'description' => 'created by seeder',
                 ]);

    }
}
