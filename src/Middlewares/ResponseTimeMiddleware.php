<?php

declare(strict_types=1);

namespace TheProject\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Example of a middleware that wraps the handler: it runs code before and after it, and
 * returns a changed copy of the response (PSR-7 responses are immutable).
 */
final class ResponseTimeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = hrtime(true);

        $response = $handler->handle($request);

        $milliseconds = (hrtime(true) - $start) / 1_000_000;

        return $response->withHeader('X-Response-Time', sprintf('%.2fms', $milliseconds));
    }
}
