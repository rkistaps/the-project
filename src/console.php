<?php

use TheApp\Factories\AppFactory;
use TheProject\Core\Factories\ContainerFactory;

define('APP_ROOT', realpath(__DIR__));

require APP_ROOT . '/vendor/autoload.php';

$container = ContainerFactory::build();
$app = AppFactory::consoleAppFromContainer($container);

$app->run($argv);