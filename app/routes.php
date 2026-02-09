<?php

declare(strict_types=1);

use App\Http\Response;
use App\Http\Router;
use App\View\View;

$router = new Router();

$router->get('/', function (\App\Http\Request $request, array $app) {
    if ($app['auth']->isLoggedIn()) {
        return Response::redirect('/dashboard');
    }
    return Response::redirect('/login');
});

$router->get('/login', function (\App\Http\Request $request, array $app) {
    if ($app['auth']->isLoggedIn()) {
        return Response::redirect('/dashboard');
    }
    $view = new View($app['config']['view_path']);
    $html = $view->render('login', ['title' => 'hello vadim!']);
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
    $html = $view->render('login', ['title' => 'hello vadim!', 'error' => 'Invalid credentials']);
    return Response::html($html);
});

$router->post('/logout', function (\App\Http\Request $request, array $app) {
    $app['auth']->logout();
    return Response::redirect('/login');
}, true);

$router->get('/dashboard', function (\App\Http\Request $request, array $app) {
    $user = $app['auth']->user($app['db']);
    $usersList = $app['auth']->listUsers($app['db']);
    $view = new View($app['config']['view_path']);
    $html = $view->render('dashboard', [
        'title' => 'Dashboard',
        'user' => $user,
        'usersList' => $usersList,
        'success' => $request->get('success'),
        'error' => $request->get('error'),
    ]);
    return Response::html($html);
}, true);

$router->post('/dashboard/click/increment', function (\App\Http\Request $request, array $app) {
    $app['auth']->incrementClickCounter($app['db']);
    return Response::redirect('/dashboard');
}, true);

$router->post('/dashboard/click/decrement', function (\App\Http\Request $request, array $app) {
    $app['auth']->decrementClickCounter($app['db']);
    return Response::redirect('/dashboard');
}, true);

$router->post('/dashboard/users/create', function (\App\Http\Request $request, array $app) {
    $username = trim((string) $request->get('username', ''));
    $password = (string) $request->get('password', '');
    
    $result = $app['auth']->createUser($app['db'], $username, $password);
    
    if ($result['success']) {
        return Response::redirect('/dashboard?success=User created successfully');
    }
    
    return Response::redirect('/dashboard?error=' . urlencode($result['error']));
}, true);

$router->post('/dashboard/users/delete', function (\App\Http\Request $request, array $app) {
    $targetId = (int) $request->get('id');
    if ($targetId > 0) {
        $app['auth']->deleteUser($app['db'], $targetId);
        $currentId = $app['auth']->userId();
        if ($currentId === $targetId) {
            $app['auth']->logout();
            return Response::redirect('/login?success=Your account was deleted');
        }
        return Response::redirect('/dashboard?success=User deleted');
    }
    return Response::redirect('/dashboard?error=Invalid user');
}, true);

$router->post('/dashboard/users/toggle-lock', function (\App\Http\Request $request, array $app) {
    $targetId = (int) $request->get('id');
    if ($targetId > 0) {
        $app['auth']->toggleUserLock($app['db'], $targetId);
        return Response::redirect('/dashboard?success=User lock updated');
    }
    return Response::redirect('/dashboard?error=Invalid user');
}, true);

$router->post('/dashboard/users/toggle-mute', function (\App\Http\Request $request, array $app) {
    $targetId = (int) $request->get('id');
    if ($targetId > 0) {
        $app['auth']->toggleUserMute($app['db'], $targetId);
        return Response::redirect('/dashboard?success=User mute updated');
    }
    return Response::redirect('/dashboard?error=Invalid user');
}, true);

return $router;
