<?php
return [
    'user'               => [
        'data-table' => [
            // delete box
            'delete'    => [
                'title'   => 'Delete User',
                'content' => 'ask_delete_user',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'cancel',
                    'delete-item',
                ],
            ],
            'claim'     => [
                'title'   => 'Claim User',
                'content' => 'ask_claim_user',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'cancel',
                    'claim',
                ],
            ],
            'send-email' => [
                'title'   => 'Send email',
                'content' => 'ask_send_email',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'cancel',
                    'send-email',
                ],
            ],
        ],
        'default'   => [
            'rating' => [
                'title'                => 'Submit User Rating',
                'message-box-template' => 'forms.user-rating',//view('forms.user-rating')->render(),
                //                'fetch-content' => route('get.form.rating.product'),
                'fetch-content'        => '/get-form-rating/user',
                // constant names from defaultActions[] or closure
                'actions'              => [
                    'cancel',
                    'accept-rating',
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
                    'cancel',
                    'delete-item',
                ],
            ],
            'launch' => [
                'title'   => 'Launch',
                'content' => 'ask_launch_event',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'cancel',
                    'launch',
                ],
            ],
        ],
    ],
];
