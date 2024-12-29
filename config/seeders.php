<?php

return [
    'core_config' => [
        'count' => 20,
    ],
    'users'       => [
        'count'       => 20,
        // max addresses per user to add
        'addresses'   => [
            'count'         => 5,
            'chance_to_add' => 90, // in percent
        ],
        'media_items' => [
            'image_storage_source_path'            => 'app/seeder/images/samples/products', // (like '/resources/images/samples/products') empty or invalid path = no image creation
            'count_avatar_images'                  => 5,// total media items per user
        ],
    ],
];
