<?php

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheApp\Components\Builders\ResponseBuilder;
use TheApp\Components\Router;
use TheApp\Interfaces\RouterInterface;
use TheProject\Handlers\Request\Demo\DemoHandler;

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

        // register routes
        $router->get('/', DemoHandler::class);

        $router->get('/lorem', fn(
            ServerRequestInterface $serverRequest,
            ResponseBuilder        $responseBuilder
        ) => $responseBuilder->withContent('ipsum')->build());

        return $router;
    }
}