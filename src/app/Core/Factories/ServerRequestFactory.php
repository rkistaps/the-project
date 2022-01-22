<?php

namespace TheProject\Core\Factories;

use Jasny\HttpMessage\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory
{
    public static function buildWithGlobals(): ServerRequestInterface
    {
        return (new ServerRequest())->withGlobalEnvironment();
    }
}
