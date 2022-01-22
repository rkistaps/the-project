<?php

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheApp\Components\CommandRunner;
use TheApp\Factories\CommandRunnerFactory;
use TheApp\Factories\ConfigFactory;
use TheApp\Interfaces\ConfigInterface;
use TheApp\Interfaces\RouterInterface;
use TheProject\Core\Factories\DatabaseFactory;
use TheProject\Core\Factories\RouterFactory;
use TheProject\Core\Factories\ServerRequestFactory;
use TheProject\Core\Factories\TemplateEngineFactory;
use TheProject\Core\Interfaces\ModelDataHydratorInterface;
use TheProject\Core\Services\ModelDataHydratorService;
use TheProject\Core\Structures\DatabaseConfig;
use Spiral\Database\Database;

return [
    ConfigInterface::class => fn(ConfigFactory $configFactory) => $configFactory->fromArray(
        require APP_ROOT . '/app/Config/config.php'
    ),
    ServerRequestInterface::class => fn() => ServerRequestFactory::buildWithGlobals(),
    RouterInterface::class => fn(RouterFactory $factory) => $factory->buildRouter(),
    Engine::class => fn(TemplateEngineFactory $factory, ConfigInterface $config) => $factory->build($config),
    DatabaseConfig::class => fn(ConfigInterface $config) => DatabaseConfig::fromArray($config->get('database', [])),
    Database::class => fn(DatabaseFactory $factory, DatabaseConfig $config) => $factory->buildFromConfig($config),
    CommandRunner::class => fn(ConfigInterface $config, CommandRunnerFactory $factory) => $factory->fromConfig($config),
    ModelDataHydratorInterface::class => fn(ContainerInterface $container) => $container->get(
        ModelDataHydratorService::class
    ),
];
