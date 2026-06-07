<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AES-256-GCM key
    |--------------------------------------------------------------------------
    |
    | 32-byte key, raw or prefixed with "base64:". When empty, a key is
    | derived from APP_KEY (suitable for local dev only).
    |
    */
    'key' => env('AES_GCM_KEY'),

    'cipher' => 'aes-256-gcm',

];
