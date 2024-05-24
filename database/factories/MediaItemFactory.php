<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\WebsiteBase\app\Models\MediaItem;

class MediaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            //            'parent_id' => fake()->boolean(80) ? fake()->randomNumber(2) : null,
            //            'store_id'         => Store::with([])->get()->first()->id,
            //            'user_id'          => User::with([])->get()->first()->id,
            'media_type'       => MediaItem::MEDIA_TYPE_IMAGE,
            'object_type'      => MediaItem::OBJECT_TYPE_PRODUCT_IMAGE,
            //            'content_code'     => null,
            'name'             => fake()->word(),
            //            'file_name'        => fake()->word() . '.png',
            //            'relative_path'    => '', //implode('/', fake()->words(3)),
            'description'      => implode(' ', fake()->words(5)),
            'meta_description' => implode(' ', fake()->words(5)),
        ];
    }
}
