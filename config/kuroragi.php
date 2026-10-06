<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Config Version
    |--------------------------------------------------------------------------
    |
    | Internal version for this config file. Used to detect stale published
    | configs after a package upgrade. Compare against the package's expected
    | version to know if the user needs to re-publish their config.
    |
    */
    'config_version' => 2,

    'activity_log_path' => storage_path('logs/activity'),
    'activity_log_file_prefix' => 'activity-',
    'roll_day' => 'monday',
    'roll_time' => '01:00', // HH:MM
    'default_reader_limit' => 50,
    'auth_model' => null, // null => use config('auth.providers.users.model')
    
    /*
    |--------------------------------------------------------------------------
    | Authorization Exception Handler
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk menangani AuthorizationException (403).
    | Ketika user mengakses halaman yang tidak diizinkan, mereka akan
    | diarahkan ke route yang ditentukan dengan pesan error.
    |
    | redirect_type: 'route', 'url', 'back', 'home'
    | - 'route': redirect ke route name (isi redirect_to dengan route name)
    | - 'url': redirect ke URL tertentu (isi redirect_to dengan URL)
    | - 'back': redirect ke halaman sebelumnya (redirect_to diabaikan)
    | - 'home': redirect ke home page '/' (redirect_to diabaikan)
    |
    */
    'authorization_exception' => [
        'enabled' => true,
        'redirect_type' => 'route', // 'route', 'url', 'back', 'home'
        'redirect_to' => 'dashboard', // route name atau URL tergantung redirect_type
        'session_key' => 'no_access', // key untuk session flash message
        'message' => 'Kamu tidak memiliki hak akses ke halaman tersebut.',
        'json_response' => true, // handle JSON/AJAX requests
    ],
];

