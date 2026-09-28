<?php

use TheApp\Factories\AppFactory;
use TheProject\Core\Factories\ContainerFactory;

require __DIR__ . '/bootstrap.php';

$container = ContainerFactory::build();
$app = AppFactory::consoleAppFromContainer($container);

$app->run($argv);
