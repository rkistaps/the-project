<?php

namespace TheProject\Core\Factories;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    public static function build(): ContainerInterface
    {
        return (new ContainerBuilder())
            ->addDefinitions(require APP_ROOT . '/app/Config/dependencies.php')
            ->build();
    }
}
