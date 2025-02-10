<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for handling CORS requests. This file
    | contains options for which origins, methods, and headers are allowed to
    | make requests to your application.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'], // Puedes limitar los métodos si lo prefieres

    'allowed_origins' => ['http://127.0.0.1:8000', 'http://127.0.0.1:5173'], // Asegúrate de agregar tus dominios

    'allowed_headers' => ['*'], // Puedes especificar headers si es necesario

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
