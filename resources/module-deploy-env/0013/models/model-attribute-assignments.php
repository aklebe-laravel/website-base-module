<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Modules\WebsiteBase\app\Models\User as UserModel;

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
                ->where('code', '=', UserModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS)
                ->first()
                ->getKey(),
            'attribute_type'     => 'array',
            'attribute_input'    => 'website-base::select_notification_channels',
            'description'        => 'Preferred Notification Channels Description',
            'form_position'      => '997',
            'form_css'           => '',
        ],
    ],
];

