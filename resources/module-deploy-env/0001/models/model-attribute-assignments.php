<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;

return [
    'version'    => '2.0',
    // class of eloquent model
    'model'      => ModelAttributeAssignment::class,
    // create data if not exists (default true)
    'can_create' => true,
    // can update data if exists and data differ (default false)
    'can_update' => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'    => ['model', 'model_attribute_id'],
    // data rows itself
    'data'       => [
        [
            'model'              => App\Models\User::class,
            'model_attribute_id' => ModelAttribute::with([])
                ->where('code', '=', 'address')
                ->first()
                ->getKey(),
            'attribute_type'     => 'integer',
            'attribute_input'    => 'website-base::select_address',
            'description'        => 'Preferred Address',
            'form_position'      => '980',
            'form_css'           => 'form-group-responsive-full',
        ],
        [
            'model'              => App\Models\User::class,
            'model_attribute_id' => ModelAttribute::with([])
                ->where('code', '=', 'user_bio')
                ->first()
                ->getKey(),
            'attribute_type'     => 'text',
            'attribute_input'    => 'textarea',
            'description'        => 'User Bio',
            'form_position'      => '1100',
            'form_css'           => 'form-group-responsive-full',
        ],
        [
            'model'              => App\Models\User::class,
            'model_attribute_id' => ModelAttribute::with([])
                ->where('code', '=', 'user_register_hint')
                ->first()
                ->getKey(),
            'attribute_type'     => 'text',
            'attribute_input'    => 'textarea',
            'description'        => 'Register Info',
            'form_position'      => '1102',
            'form_css'           => 'form-group-responsive-full',
        ],
    ],
];

