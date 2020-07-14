<?php

use League\Plates\Engine;
use Spiral\Database\Driver\MySQL\MySQLDriver;
use TheApp\Components\Router;
use TheApp\Factories\ConfigFactory;
use TheApp\Factories\RouterFactory;
use TheApp\Interfaces\ConfigInterface;
use TheProject\Factories\TemplateEngineFactory;
use TheProject\Core\Structures\DatabaseConfig;

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
    DatabaseConfig::class => function (ConfigInterface $config) {
        return DatabaseConfig::fromArray($config->get('database', []));
    },
    Spiral\Database\Database::class => function (DatabaseConfig $databaseConfig) {
        $driver = new MySQLDriver([
            'connection' => 'mysql:host=' . $databaseConfig->host . ';dbname=' . $databaseConfig->name,
            'username' => $databaseConfig->username,
            'password' => $databaseConfig->password,
        ]);

        return (new Spiral\Database\Database(
            $databaseConfig->name,
            '',
            $driver
        ));
    },
];
