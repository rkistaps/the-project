<?php

namespace TheProject\Core\Factories;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ContainerFactory
{
    private static ?ContainerInterface $container = null;

    public static function build(): ContainerInterface
    {
        return self::$container = (new ContainerBuilder())
            ->addDefinitions(require APP_ROOT . '/app/Config/dependencies.php')
            ->build();
    }

    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been built yet');
        }

        return self::$container;
    }
}
