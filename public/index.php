<?php

define('APP_ROOT', realpath(__DIR__ . '/..'));

use Psr\Http\Message\ServerRequestInterface;
use TheApp\Apps\WebApp;
use TheApp\Components\HttpResponseEmitter;
use TheApp\Interfaces\RouterInterface;
use TheProject\Core\Factories\ContainerFactory;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require APP_ROOT . '/vendor/autoload.php';

// Debug page for uncaught errors. Whoops is a dev dependency, so a --no-dev install skips it.
if (class_exists(Run::class)) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();
}

$container = ContainerFactory::build();

$request = $container->get(ServerRequestInterface::class);
$router = $container->get(RouterInterface::class);

$app = $container->get(WebApp::class);
$response = $app->run($request, $router);

$emitter = $container->get(HttpResponseEmitter::class);
$emitter->emit($response);