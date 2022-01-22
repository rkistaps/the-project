<?php

use League\Plates\Engine;
use TheApp\Components\CommandRunner;
use TheApp\Factories\CommandRunnerFactory;
use TheApp\Factories\ConfigFactory;
use TheApp\Interfaces\ConfigInterface;
use TheProject\Core\Factories\DatabaseFactory;
use TheProject\Core\Factories\TemplateEngineFactory;
use TheProject\Core\Structures\DatabaseConfig;

return [
    ConfigInterface::class => fn(ConfigFactory $configFactory) => $configFactory->fromArray(require APP_ROOT . '/app/Config/config.php'),
    Engine::class => fn(TemplateEngineFactory $factory, ConfigInterface $config) => $factory->build($config),
    DatabaseConfig::class => fn(ConfigInterface $config) => DatabaseConfig::fromArray($config->get('database', [])),
    Spiral\Database\Database::class => fn(DatabaseFactory $factory, DatabaseConfig $config) => $factory->buildFromConfig($config),
    CommandRunner::class => fn(ConfigInterface $config, CommandRunnerFactory $factory) => $factory->fromConfig($config),
];
