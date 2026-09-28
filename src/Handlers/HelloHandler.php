<?php

declare(strict_types=1);

namespace TheProject\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;

/**
 * Example of reading a route parameter: the router adds [a:name] from the path as a request attribute.
 */
final class HelloHandler implements RequestHandlerInterface
{
    public function __construct(private ResponseBuilder $responseBuilder) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = (string) $request->getAttribute('name');

        return $this->responseBuilder
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withContent('Hello, ' . $name . '!')
            ->build();
    }
}
