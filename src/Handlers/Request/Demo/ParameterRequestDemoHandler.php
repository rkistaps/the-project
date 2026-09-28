<?php

namespace TheProject\Handlers\Request\Demo;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;

class ParameterRequestDemoHandler implements RequestHandlerInterface
{
    private ResponseBuilder $responseBuilder;

    public function __construct(Engine $template, ResponseBuilder $responseBuilder)
    {
        $this->template = $template;
        $this->responseBuilder = $responseBuilder;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseBuilder->withContent("Parameter given: " . $request->getAttribute('id'))->build();
    }
}