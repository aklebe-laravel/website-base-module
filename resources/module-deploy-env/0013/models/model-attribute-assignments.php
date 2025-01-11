<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;

return [
    // class of eloquent model
    'model'   => ModelAttributeAssignment::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['model', 'model_attribute_id'],
    // data rows itself
    'data'    => [
        [
            'model'              => 'App\Models\User',
            'model_attribute_id' => ModelAttribute::with([])
                                                  ->where('code', '=', 'preferred_notification_channels')
                                                  ->first()
                                                  ->getKey(),
            'attribute_type'     => 'array',
            'attribute_input'    => 'website-base::select_notification_channels',
            'description'        => 'Preferred Notification Channels',
            'form_position'      => '992',
            'form_css'           => 'col-12 col-md-6',
        ],
    ],
];

