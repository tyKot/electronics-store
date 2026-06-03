<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // 👇 К каким путям применять CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 👇 Разрешённые HTTP-методы
    'allowed_methods' => ['*'], // или ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']

    // 👇 Разрешённые источники (домены Botpress)
    'allowed_origins' => [
        'https://cdn.botpress.cloud',
        'https://botpress.cloud',
        'https://*.botpress.cloud',
    ],

    // 👇 Разрешённые источники по regex (для поддоменов Botpress)
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.botpress\.cloud$/',
        '/^https:\/\/.*\.bpcontent\.cloud$/',
        '/^https:\/\/botpress\.cloud$/',
    ],

    // ВАЖНО: добавляем ваш кастомный заголовок
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
        'X-Bot-Token',  // 👈 Обязательно!
    ],

    'exposed_headers' => [
        'X-Request-URL',
        'X-Final-URL',
    ],

    // 👇 Максимальное время кэширования preflight-запроса (в секундах)
    'max_age' => 60 * 60 * 24, // 24 часа

    // 👇 Разрешить отправку credentials (cookies, Authorization header)
    'supports_credentials' => false,
];
