<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use TheProject\Core\Factories\ContainerFactory;

function getContainer(): ContainerInterface
{
    return ContainerFactory::getContainer();
}
