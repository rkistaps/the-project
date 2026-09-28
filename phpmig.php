<?php

use \Phpmig\Adapter;
use Psr\Container\ContainerInterface;
use TheProject\Core\Factories\ContainerFactory;

define('APP_ROOT', realpath(__DIR__));

require __DIR__ . '/vendor/autoload.php';

$container = new ArrayObject();

// replace this with a better Phpmig\Adapter\AdapterInterface
$container['phpmig.adapter'] = new Adapter\File\Flat(__DIR__ . '/migrations/.migrations.log');
$container['phpmig.migrations_path'] = __DIR__ . '/migrations';
$container[ContainerInterface::class] = ContainerFactory::build();

return $container;
