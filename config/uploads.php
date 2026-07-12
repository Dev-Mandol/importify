<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CSV Upload Configuration
    |--------------------------------------------------------------------------
    */

    'max_size' => (int) env('CSV_MAX_UPLOAD_SIZE', 10240), // in Kilobytes, default 10MB

    'allowed_mimes' => [
        'text/csv',
        'text/plain',
        'application/vnd.ms-excel',
    ],

    'disk' => env('CSV_UPLOAD_DISK', 'uploads'),
];
