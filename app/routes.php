<?php

declare(strict_types=1);

use App\Http\Response;
use App\Http\Router;
use App\View\View;

$router = new Router();

$router->get('/', function (\App\Http\Request $request, array $app) {
    $view = new View($app['config']['view_path']);
    $html = $view->render('home', ['title' => 'Home']);
    return Response::html($html);
});

$router->get('/login', function (\App\Http\Request $request, array $app) {
    if ($app['auth']->isLoggedIn()) {
        return Response::redirect('/dashboard');
    }
    $view = new View($app['config']['view_path']);
    $html = $view->render('login', ['title' => 'Log in']);
    return Response::html($html);
});

$router->post('/login', function (\App\Http\Request $request, array $app) {
    $username = trim((string) $request->get('username', ''));
    $password = (string) $request->get('password', '');
    $userId = $app['auth']->attempt($app['db'], $username, $password);
    if ($userId !== null) {
        $app['auth']->login($app['db'], $userId);
        return Response::redirect('/dashboard');
    }
    $view = new View($app['config']['view_path']);
    $html = $view->render('login', ['title' => 'Log in', 'error' => 'Invalid credentials']);
    return Response::html($html);
});

$router->post('/logout', function (\App\Http\Request $request, array $app) {
    $app['auth']->logout();
    return Response::redirect('/');
}, true);

$router->get('/dashboard', function (\App\Http\Request $request, array $app) {
    $view = new View($app['config']['view_path']);
    $html = $view->render('dashboard', [
        'title' => 'Dashboard',
        'userId' => $app['auth']->userId(),
    ]);
    return Response::html($html);
}, true);

return $router;
