<?php

namespace TheProject\Core\Factories;

use DI\Container;
use DI\ContainerBuilder;

class ContainerFactory
{
    public static function build(): Container
    {
        return (new ContainerBuilder())
            ->addDefinitions(require APP_ROOT . '/config/dependencies.php')
            ->build();
    }
}
