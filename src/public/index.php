<?php

define('APP_ROOT', realpath(__DIR__ . '/..'));

use Jasny\HttpMessage\Emitter;
use Psr\Http\Message\ServerRequestInterface;
use TheApp\Apps\WebApp;
use TheApp\Interfaces\RouterInterface;
use TheProject\Core\Factories\ContainerFactory;

require APP_ROOT . '/vendor/autoload.php';

$container = ContainerFactory::build();

$request = $container->get(ServerRequestInterface::class);
$router = $container->get(RouterInterface::class);

$app = $container->get(WebApp::class);
$response = $app->run($request, $router);

$emitter = new Emitter();
$emitter->emit($response);