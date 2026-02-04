<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

$app = bootstrap();
$request = \App\Http\Request::fromGlobals();
$router = $app['router'];

$match = $router->match($request);

if ($match === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>404 Not Found</h1>';
    return;
}

[$handler, $requiresAuth] = $match;
$auth = $app['auth'];

if ($requiresAuth && !$auth->isLoggedIn()) {
    $response = \App\Http\Response::redirect($auth->loginPath());
    $response->send();
    return;
}

$response = $handler($request, $app);
$response->send();
