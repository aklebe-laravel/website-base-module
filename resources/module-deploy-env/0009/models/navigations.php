<?php

return [
    'version'     => '2.0',
    // class of eloquent model
    'model'       => \Modules\WebsiteBase\app\Models\Navigation::class,
    // columns to check if data already exists (AND WHERE)
    'uniques'     => ['code'],
    // to delete
    'auto_delete' => [
        'Admin-Emails-Menu-L2',
        'Admin-Email-Templates-L3',
        'Admin-Concerns-L3',
    ],
];
