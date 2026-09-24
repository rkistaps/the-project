<?php

declare(strict_types=1);

namespace TheProject\Routes;

use TheApp\Components\Router;
use TheApp\Interfaces\RouterConfiguratorInterface;
use TheProject\Handlers\Api\CreateUserHandler;
use TheProject\Handlers\Api\ListUsersHandler;
use TheProject\Handlers\Api\ShowUserHandler;
use TheProject\Middlewares\JsonBodyMiddleware;

/**
 * Routes of the JSON API, all under /api. Its errors are JSON too: see ApplicationFactory::api().
 */
final class ApiRoutes implements RouterConfiguratorInterface
{
    public const BASE_PATH = '/api';

    public function configureRouter(Router $router): void
    {
        $api = $router->withBasePath(self::BASE_PATH);

        // Example resource on the User model
        $api->get('/users', ListUsersHandler::class, 'api.users.list');
        $api->get('/users/[i:id]', ShowUserHandler::class, 'api.users.show');
        $api->post('/users', CreateUserHandler::class, 'api.users.create')
            ->withMiddleware(JsonBodyMiddleware::class);
    }
}
