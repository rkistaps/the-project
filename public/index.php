<?php

use Psr\Http\Message\ServerRequestInterface;
use TheApp\Components\HttpResponseEmitter;
use TheProject\Core\Factories\ApplicationFactory;
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

$response = ApplicationFactory::web($container)
    ->run($container->get(ServerRequestInterface::class));

$container->get(HttpResponseEmitter::class)->emit($response);
