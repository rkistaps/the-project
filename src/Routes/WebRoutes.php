<?php

declare(strict_types=1);

namespace TheProject\Routes;

use TheApp\Components\Router;
use TheApp\Interfaces\RouterConfiguratorInterface;
use TheProject\Handlers\HelloHandler;
use TheProject\Handlers\HomeHandler;
use TheProject\Handlers\LoginFormHandler;
use TheProject\Handlers\LoginHandler;
use TheProject\Handlers\LogoutHandler;
use TheProject\Middlewares\ResponseTimeMiddleware;

/**
 * Routes of the website. Add a route here, or add another configurator to ApplicationFactory::web().
 * Every page but the login page is for signed-in users only: see AuthMiddleware.
 */
final class WebRoutes implements RouterConfiguratorInterface
{
    public const HOME_PATH = '/';
    public const LOGIN_PATH = '/login';

    public function configureRouter(Router $router): void
    {
        // The dashboard: a request handler class that renders a template, with a middleware around it
        $router->get(self::HOME_PATH, HomeHandler::class, 'home')
            ->addMiddleware(ResponseTimeMiddleware::class);

        $router->get(self::LOGIN_PATH, LoginFormHandler::class, 'login');
        $router->post(self::LOGIN_PATH, LoginHandler::class, 'login.submit');
        $router->post('/logout', LogoutHandler::class, 'logout');

        // A route parameter: [a:name] is alphanumeric, and reaches the handler as a request attribute
        $router->get('/hello/[a:name]', HelloHandler::class, 'hello');
    }
}
