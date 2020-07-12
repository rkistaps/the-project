<?php

namespace TheProject\Config\RouteConfigurators;

use Psr\Http\Message\RequestInterface;
use TheApp\Components\Builders\ResponseBuilder;
use TheApp\Components\Router;
use TheApp\Interfaces\RouterConfiguratorInterface;

class DemoRouteConfigurator implements RouterConfiguratorInterface
{
    public function configureRouter(Router $router)
    {
        $router->get('/',
            function (RequestInterface $request, ResponseBuilder $responseBuilder) {
                return $responseBuilder->withContent('Hi, mom!')->build();
            });
    }
}
