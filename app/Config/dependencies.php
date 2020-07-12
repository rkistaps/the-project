<?php

use League\Plates\Engine;
use TheApp\Components\Router;
use TheApp\Factories\ConfigFactory;
use TheApp\Factories\RouterFactory;
use TheApp\Interfaces\ConfigInterface;
use TheProject\Factories\TemplateEngineFactory;

return [
    ConfigInterface::class => function (ConfigFactory $configFactory) {
        return $configFactory->fromArray(require APP_ROOT . '/app/Config/config.php');
    },
    Router::class => function (RouterFactory $routerFactory, ConfigInterface $config) {
        return $routerFactory->buildFromConfig($config);
    },
    Engine::class => function (TemplateEngineFactory $factory, ConfigInterface $config) {
        return $factory->build($config);
    },
];
