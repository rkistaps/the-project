<?php

namespace TheProject\Handlers\Demo;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;

/**
 * Class DemoHandler
 * @package TheProject\Handlers\Demo
 */
class DemoHandler implements RequestHandlerInterface
{
    private Engine $template;
    private ResponseBuilder $responseBuilder;

    public function __construct(Engine $template, ResponseBuilder $responseBuilder)
    {
        $this->template = $template;
        $this->responseBuilder = $responseBuilder;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseBuilder->withContent($this->template->render('Demo/index'))->build();
    }
}
