<?php

declare(strict_types=1);

namespace TheProject\Handlers;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;

/**
 * Example of a request handler class that renders a template. Its constructor dependencies
 * are injected from the container.
 */
final class HomeHandler implements RequestHandlerInterface
{
    public function __construct(
        private Engine $templates,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseBuilder
            ->withContent($this->templates->render('home'))
            ->build();
    }
}
