<?php

return [
    'access_token_url' => env('PAYFAST_ACCESS_TOKEN_URL', 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/GetAccessToken'),
    'transaction_url'  => env('PAYFAST_TRANSACTION_URL', 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/PostTransaction')
];
