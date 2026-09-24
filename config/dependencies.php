<?php

use League\Plates\Engine;
use Nyholm\Psr7\Factory\Psr17Factory;
use Opis\Database\Database;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use TheApp\Factories\ConfigFactory;
use TheApp\Interfaces\ConfigInterface;
use TheProject\Core\Factories\DatabaseFactory;
use TheProject\Core\Factories\ServerRequestFactory;
use TheProject\Core\Factories\TemplateEngineFactory;
use TheProject\Core\Interfaces\ModelDataHydratorInterface;
use TheProject\Core\Logging\ErrorLogLogger;
use TheProject\Core\Services\ModelDataHydratorService;
use TheProject\Core\Structures\DatabaseConfig;

return [
    ConfigInterface::class => fn(ConfigFactory $configFactory) => $configFactory->fromArray(require APP_ROOT . '/config/config.php'),
    ServerRequestInterface::class => fn() => ServerRequestFactory::buildWithGlobals(),
    ResponseFactoryInterface::class => fn(Psr17Factory $factory) => $factory,
    StreamFactoryInterface::class => fn(Psr17Factory $factory) => $factory,
    Engine::class => fn(TemplateEngineFactory $factory, ConfigInterface $config) => $factory->build($config),
    DatabaseConfig::class => fn(ConfigInterface $config) => DatabaseConfig::fromArray($config->get('database', [])),
    Database::class => fn(DatabaseFactory $factory, DatabaseConfig $config) => $factory->buildFromConfig($config),
    LoggerInterface::class => fn(ErrorLogLogger $logger) => $logger,
    ModelDataHydratorInterface::class => fn(ContainerInterface $container) => $container->get(ModelDataHydratorService::class),
];
