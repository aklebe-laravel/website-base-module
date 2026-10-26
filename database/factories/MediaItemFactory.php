<?php

namespace Modules\WebsiteBase\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\WebsiteBase\app\Models\MediaItem;

class MediaItemFactory extends Factory
{
    protected $model = MediaItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'media_type'       => MediaItem::MEDIA_TYPE_IMAGE,
            'object_type'      => MediaItem::OBJECT_TYPE_PRODUCT_IMAGE,
            'name'             => fake()->word(),
            'description'      => implode(' ', fake()->words(5)),
            'meta_description' => implode(' ', fake()->words(5)),
        ];
    }
}
