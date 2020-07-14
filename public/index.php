<?php

define('APP_ROOT', realpath(__DIR__ . '/..'));

use Jasny\HttpMessage\Emitter;
use TheApp\Factories\AppFactory;
use TheProject\Core\Factories\ContainerFactory;
use TheProject\Core\Factories\ServerRequestFactory;

require APP_ROOT . '/vendor/autoload.php';

$container = ContainerFactory::build();
$app = AppFactory::fromContainer($container);

$request = ServerRequestFactory::buildWithGlobals();
$response = $app->run($request);

$emitter = new Emitter();
$emitter->emit($response);