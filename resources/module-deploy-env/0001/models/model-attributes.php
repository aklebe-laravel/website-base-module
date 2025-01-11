<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;

return [
    // class of eloquent model
    'model'   => ModelAttribute::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['code'], // Not ['module','code'] in this version!
    // data rows itself
    'data'    => [
        [
            'module'      => 'website-base',
            'code'        => 'name',
            'description' => 'Name of this model',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'negotiable',
            'description' => 'Negotiable',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'email',
            'description' => 'Email address',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'firstname',
            'description' => 'Firstname',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'lastname',
            'description' => 'Lastname',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'description',
            'description' => 'Description',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'extended_description',
            'description' => 'Extended Description',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'short_description',
            'description' => 'Short description',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'meta_description',
            'description' => 'Meta description',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'url',
            'description' => 'Url',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'image',
            'description' => 'Image',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'price',
            'description' => 'Price',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'currency',
            'description' => 'Currency',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'qty',
            'description' => 'Quantity',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'payment_method',
            'description' => 'Payment method',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'address',
            'description' => 'Address',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'shipping_method',
            'description' => 'Shipping method',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'user_bio',
            'description' => 'User Bio',
        ],
        [
            'module'      => 'website-base',
            'code'        => 'user_register_hint',
            'description' => 'User Register Hint',
        ],
    ],
];

