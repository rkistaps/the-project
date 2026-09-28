<?php

use Psr\Http\Message\ServerRequestInterface;
use TheApp\Components\HttpResponseEmitter;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Factories\ContainerFactory;
use TheProject\Core\Helpers\Env;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require __DIR__ . '/../bootstrap.php';

if (Env::bool('APP_DEBUG')) {
    // Debug page for uncaught errors. Whoops is a dev dependency, so a --no-dev install
    // never has it, whatever APP_DEBUG says.
    if (class_exists(Run::class)) {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
    }
} else {
    // The apps' error handlers render error pages; anything that still escapes them is logged, never shown
    ini_set('display_errors', '0');
}

$container = ContainerFactory::build();

$request = $container->get(ServerRequestInterface::class);
$response = ApplicationFactory::forRequest($container, $request)->run($request);

$container->get(HttpResponseEmitter::class)->emit($response);
