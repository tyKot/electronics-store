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
    ],

    // 👇 Разрешённые источники по regex (для поддоменов Botpress)
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.botpress\.cloud$/',
        '/^https:\/\/.*\.bpcontent\.cloud$/',
    ],

    // 👇 Разрешённые заголовки
    'allowed_headers' => [
        'Content-Type',
        'X-Auth-Token',
        'Origin',
        'Authorization',
        'X-Requested-With',
        'X-Bot-Token',  // 👈 Ваш кастомный токен для бота
    ],

    // 👇 Заголовки, доступные для чтения браузером
    'exposed_headers' => [],

    // 👇 Максимальное время кэширования preflight-запроса (в секундах)
    'max_age' => 60 * 60 * 24, // 24 часа

    // 👇 Разрешить отправку credentials (cookies, Authorization header)
    'supports_credentials' => false,
];
