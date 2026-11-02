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
                ->where('code', '=', 'timezone')
                ->first()
                ->getKey(),
            'attribute_type'     => 'string',
            'attribute_input'    => 'website-base::select_timezone',
            'description'        => 'Time zone of the times shown to you (empty: time zone of the installation)',
            'form_position'      => '2000',
            'form_css'           => '',
            'default_value'      => '',
        ],
    ],
];
