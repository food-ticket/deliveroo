<?php

return [
    'base_url' => env('DELIVEROO_BASE_URL', 'https://api.developers.deliveroo.com'),

    'auth_url' => env('DELIVEROO_AUTH_URL', 'https://auth.developers.deliveroo.com/oauth2/token'),

    'client_id' => env('DELIVEROO_CLIENT_ID'),

    'client_secret' => env('DELIVEROO_CLIENT_SECRET'),

    'webhook_secret' => env('DELIVEROO_WEBHOOK_SECRET'),
];
