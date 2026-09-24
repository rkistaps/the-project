<?php

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use TheApp\Components\Router;
use TheApp\Interfaces\RouterInterface;
use TheProject\Handlers\HelloHandler;
use TheProject\Handlers\HomeHandler;
use TheProject\Middlewares\ResponseTimeMiddleware;

class RouterFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildRouter(): RouterInterface
    {
        $router = $this->container->get(Router::class);

        // A request handler class that renders a template, with a middleware around it
        $router->get('/', HomeHandler::class, 'home')
            ->withMiddleware(ResponseTimeMiddleware::class);

        // A route parameter: [a:name] is alphanumeric, and reaches the handler as a request attribute
        $router->get('/hello/[a:name]', HelloHandler::class, 'hello');

        return $router;
    }
}
