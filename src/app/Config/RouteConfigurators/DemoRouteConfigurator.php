<?php

namespace TheProject\Config\RouteConfigurators;

use Psr\Http\Message\RequestInterface;
use TheApp\Components\Builders\ResponseBuilder;
use TheApp\Components\Router;
use TheApp\Interfaces\RouterConfiguratorInterface;
use TheProject\Handlers\Demo\DemoDbHandler;
use TheProject\Handlers\Demo\DemoHandler;
use TheProject\Middlewares\RandomAccessMiddleware;

class DemoRouteConfigurator implements RouterConfiguratorInterface
{
    private const BASE_PATH = '/demo';
    public function configureRouter(Router $router)
    {
        // callback request handler
        $router->get(self::BASE_PATH . '/',
            function (RequestInterface $request, ResponseBuilder $responseBuilder) {
                return $responseBuilder->withContent("Hi, mom! I'm at " . $request->getUri()->getPath())->build();
            });
        // regular request handler
        $router->get(self::BASE_PATH . '/handler', DemoHandler::class);
        // test random access middleware
        $router->get(self::BASE_PATH . '/random-access', DemoHandler::class)
            ->withMiddleware(RandomAccessMiddleware::class);
        $router->get(self::BASE_PATH . '/database', DemoDbHandler::class);
    }
}