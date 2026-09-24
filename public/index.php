<?php

use Psr\Http\Message\ServerRequestInterface;
use TheApp\Apps\WebApp;
use TheApp\Components\HttpResponseEmitter;
use TheApp\Interfaces\RouterInterface;
use TheProject\Core\Factories\ContainerFactory;
use TheProject\Core\Helpers\Env;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require __DIR__ . '/../bootstrap.php';

// Debug page for uncaught errors, only with APP_DEBUG on. Whoops is a dev dependency, so a
// --no-dev install never has it, whatever APP_DEBUG says.
if (Env::bool('APP_DEBUG') && class_exists(Run::class)) {
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
