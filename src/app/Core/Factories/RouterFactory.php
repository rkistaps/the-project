<?php

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;
use TheApp\Components\Router;
use TheApp\Interfaces\RouterInterface;
use TheProject\Handlers\Request\Demo\DemoDbHandler;
use TheProject\Handlers\Request\Demo\DemoHandler;
use TheProject\Handlers\Request\Demo\ParameterRequestDemoHandler;
use TheProject\Middlewares\DemoMiddleware;
use TheProject\Middlewares\InnerMiddleware;
use TheProject\Middlewares\OuterMiddleware;
use TheProject\Middlewares\RandomAccessMiddleware;
use TheProject\Middlewares\AuthorizedUserMiddleware;

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

        // register route with request handler - http://the-project.test/
        $router->get('/', DemoHandler::class);

        // register route with anonymous function - http://the-project.test/lorem
        $router->get('/lorem', fn(
            ServerRequestInterface $serverRequest,
            ResponseBuilder $responseBuilder
        ) => $responseBuilder->withContent('ipsum')->build());

        // register route with a parameter - http://the-project.test/lorem/15/ipsum
        $router->get('/lorem/[i:id]/ipsum', ParameterRequestDemoHandler::class);

        // register route with request handler - http://the-project.test/db
        $router->get('/db', DemoDbHandler::class);

        // register route with a middleware - http://the-project.test/middleware
        $router
            ->get('/middleware', DemoHandler::class)
            ->withMiddleware(DemoMiddleware::class);

        // register route with access middleware - http://the-project.test/access-middleware
        $router
            ->get('/access-middleware', DemoHandler::class)
            ->withMiddleware(RandomAccessMiddleware::class);

        // register route with anonymous middleware - http://the-project.test/anonymous-middleware
        $router
            ->get('/anonymous-middleware', DemoHandler::class)
            ->withMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                $response = $handler->handle($request);

                $response->getBody()->write('<br />after');

                return $response;
            });

        // register route with multiple middlewares - http://the-project.test/authorized-user
        $router->get('/authorized-user', function (
            ServerRequestInterface $serverRequest,
            ResponseBuilder $responseBuilder
        ) {
            return $responseBuilder
                ->withContent('Authorized user: ' . $serverRequest->getAttribute('authorized-user'))
                ->build();
        })->withMiddleware(AuthorizedUserMiddleware::class);

        // register route with multiple middlewares - http://the-project.test/multiple-middlewares
        $router->get('/multiple-middlewares', DemoHandler::class)
            ->withMiddleware(OuterMiddleware::class)
            ->withMiddleware(InnerMiddleware::class);

        return $router;
    }
}