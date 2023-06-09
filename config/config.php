<?php

return [
    /*
     * The default key used for all file encryption / decryption
     * This package will look for a FILECRYPT_KEY in your env file
     * If no FILECRYPT_KEY is found, then it will use your Laravel APP_KEY
     */
    'key' => env('FILECRYPT_KEY', env('APP_KEY')),

    /*
     * The algorithm used for encryption.
     * Supported options are AES-128-CBC and AES-256-CBC
     */
    'algorithm' => 'AES-256-CBC',

    /*
     * The Storage disk used by default to locate your files.
     */
    'disk' => 'local',
];
