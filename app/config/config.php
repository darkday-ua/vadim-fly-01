<?php

declare(strict_types=1);

return [
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => (bool) (getenv('APP_DEBUG') ?: '0'),
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),

    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: '3306'),
        'name' => getenv('DB_NAME') ?: 'fly',
        'user' => getenv('DB_USER') ?: 'fly',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],

    'session' => [
        'name' => getenv('SESSION_NAME') ?: 'fly_session',
        'lifetime' => (int) (getenv('SESSION_LIFETIME') ?: '7200'),
    ],

    'auth' => [
        'login_path' => getenv('LOGIN_PATH') ?: '/login',
    ],

    'view_path' => realpath(dirname(__DIR__) . '/src/View/templates') ?: dirname(__DIR__) . '/src/View/templates',
];
