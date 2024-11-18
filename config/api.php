<?php
    return [
        'app_key'                   => env('APP_KEY'),
        'api_timeout_in_secs'       => env('API_TIMEOUT_IN_SECS', 10),
        'api_limit'                 => env('API_LIMIT', 60),
        'max_attempts'              => env('MAXATTEMPTS', 5),
        'paginate_limit'            => env('PAGINATE_LIMIT', 25),
        'presigned_url_expiration'  => env('PRESIGNED_URL_EXPIRATION', 60),
        'passport_client_id'        => env('PASSPORT_CLIENT_ID', ''),
        'passport_client_secret'    => env('PASSPORT_CLIENT_SECRET', ''),
        'max_voucher_count'         => env('MAX_VOUCHER_COUNT', 10),
        'voucher_chars_count'       => env('VOUCHER_CODE_CHARS_COUNT', 5),
    ];