<?php

namespace TheProject\Core\Factories;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory
{
    public static function buildWithGlobals(): ServerRequestInterface
    {
        $factory = new Psr17Factory();

        return (new ServerRequestCreator($factory, $factory, $factory, $factory))->fromGlobals();
    }
}
