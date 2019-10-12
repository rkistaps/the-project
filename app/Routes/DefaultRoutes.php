<?php

namespace TheProject\Routes;

use TheApp\Components\Router;
use TheApp\Interfaces\RouteConfiguratorInterface;
use TheProject\Handlers\Demo\DemoHandler;

/**
 * Class DefaultRoutes
 * @package TheApp\Routes
 */
class DefaultRoutes implements RouteConfiguratorInterface
{
    /**
     * Map routes
     * @param Router $router
     * @return void
     * @throws \Exception
     */
    public function configureRoutes(Router $router)
    {
        $router->any('/', DemoHandler::class);
    }
}
