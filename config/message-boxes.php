<?php
return [
    'user'               => [
        'data-table' => [
            // delete box
            'delete'     => [
                'title'   => 'Delete User',
                'content' => 'ask_delete_user',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'system-base::delete-item',
                ],
            ],
            'claim'      => [
                'title'   => 'Claim User',
                'content' => 'ask_claim_user',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'system-base::claim',
                ],
            ],
            'send-email' => [
                'title'   => 'Send email',
                'content' => 'ask_send_email',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'website-base::send-email',
                ],
            ],
        ],
    ],
    'notification-event' => [
        'data-table' => [
            // delete box
            'delete' => [
                'title'   => 'Delete Event',
                'content' => 'ask_delete_event',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'system-base::delete-item',
                ],
            ],
            'launch' => [
                'title'   => 'Launch',
                'content' => 'ask_launch_event',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'system-base::launch',
                ],
            ],
        ],
    ],
    'media-item'         => [
        'data-table' => [
            //'upload' => [
            //    'title'   => 'Upload',
            //    'content' => 'launch_import_description',
            //    // constant names from defaultActions[] or closure
            //    'actions' => [
            //        'cancel',
            //        'upload',
            //    ],
            //],
            'import' => [
                'title'   => 'Import',
                'content' => 'launch_import_description',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'website-base::import',
                ],
            ],
        ],
    ],
];
