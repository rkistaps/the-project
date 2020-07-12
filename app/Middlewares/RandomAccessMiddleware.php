<?php

namespace TheProject\Middlewares;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;

class RandomAccessMiddleware implements MiddlewareInterface
{
    private ResponseBuilder $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (rand(0, 1)) {
            return $this->responseBuilder->withContent('Im a middleware')->build();
        }

        return $handler->handle($request);
    }
}