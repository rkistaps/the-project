<?php

define('APP_ROOT', realpath(__DIR__ . '/..'));

use DI\ContainerBuilder;
use Jasny\HttpMessage\Emitter;
use Jasny\HttpMessage\ServerRequest;
use TheApp\Factories\AppFactory;

require APP_ROOT . '/vendor/autoload.php';

$container = (new ContainerBuilder())
    ->addDefinitions(require APP_ROOT . '/app/Config/dependencies.php')
    ->build();

$app = AppFactory::fromContainer($container);

$request = (new ServerRequest())->withGlobalEnvironment();

$response = $app->run($request);

$emitter = new Emitter();
$emitter->emit($response);