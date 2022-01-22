<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use TheApp\Apps\App;

function getContainer(): ContainerInterface
{
    return App::getContainer();
}
