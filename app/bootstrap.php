<?php

declare(strict_types=1);

/**
 * Bootstrap: load config, create DB connection, start session.
 * Returns a container-like array for request lifecycle (no global state).
 */
function bootstrap(): array
{
    $root = dirname(__DIR__);  // repo root (fly/) when bootstrap is at fly/app/bootstrap.php
    $appDir = $root . '/app';

    require_once $root . '/vendor/autoload.php';

    $config = require $appDir . '/config/config.php';

    try {
        $pdo = \App\Db\Connection::fromConfig($config['db']);
        $connection = new \App\Db\Connection($pdo);
    } catch (\PDOException $e) {
        if (str_contains($e->getMessage(), 'could not find driver')) {
            header('Content-Type: text/html; charset=utf-8');
            http_response_code(503);
            echo '<h1>PHP MySQL driver missing</h1><p>Install it and restart the server, e.g. <code>sudo apt install php-mysql</code> or <code>php8.1-mysql</code>.</p>';
            exit;
        }
        throw $e;
    }

    session_name($config['session']['name']);
    session_set_cookie_params([
        'lifetime' => $config['session']['lifetime'],
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $auth = new \App\Auth\Auth($config['auth']);

    $router = require $appDir . '/routes.php';

    return [
        'config' => $config,
        'db' => $connection,
        'auth' => $auth,
        'router' => $router,
    ];
}
