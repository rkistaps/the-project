<?php

declare(strict_types=1);

namespace TheProject\Routes;

use TheApp\Components\Router;
use TheApp\Interfaces\RouterConfiguratorInterface;
use TheProject\Handlers\HelloHandler;
use TheProject\Handlers\HomeHandler;
use TheProject\Middlewares\ResponseTimeMiddleware;

/**
 * Routes of the web app. Add a route here, or add another configurator to ApplicationFactory::web().
 */
final class WebRoutes implements RouterConfiguratorInterface
{
    public function configureRouter(Router $router): void
    {
        // A request handler class that renders a template, with a middleware around it
        $router->get('/', HomeHandler::class, 'home')
            ->withMiddleware(ResponseTimeMiddleware::class);

        // A route parameter: [a:name] is alphanumeric, and reaches the handler as a request attribute
        $router->get('/hello/[a:name]', HelloHandler::class, 'hello');
    }
}
