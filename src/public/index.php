<?php

define('APP_ROOT', realpath(__DIR__ . '/..'));

use Jasny\HttpMessage\Emitter;
use Psr\Http\Message\ServerRequestInterface;
use TheApp\Components\Builders\ResponseBuilder;
use TheApp\Components\Router;
use TheApp\Factories\AppFactory;
use TheProject\Core\Factories\ContainerFactory;
use TheProject\Core\Factories\ServerRequestFactory;
use TheProject\Handlers\Request\Demo\DemoHandler;

require APP_ROOT . '/vendor/autoload.php';

$container = ContainerFactory::build();
$app = AppFactory::webAppFromContainer($container);

$request = ServerRequestFactory::buildWithGlobals();

// register routes
$router = $container->get(Router::class);
$router->get('/', DemoHandler::class);

$router->get('/lorem', function (
    ServerRequestInterface $serverRequest,
    ResponseBuilder $responseBuilder
) {
    return $responseBuilder->withContent('ipsum')->build();
});

$response = $app->run($request, $router);

$emitter = new Emitter();
$emitter->emit($response);