<?php

declare(strict_types=1);

namespace TheProject\Http;

use League\Plates\Engine;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Builds website responses: rendered templates and redirects. Unlike ResponseBuilder it keeps no response
 * of its own, so the one instance the container shares can serve any number of requests.
 */
final class HtmlResponder
{
    public function __construct(
        private Engine $templates,
        private ResponseFactoryInterface $responses,
        private StreamFactoryInterface $streams,
    ) {}

    /**
     * @param array<string, mixed> $data The template's variables
     */
    public function render(string $template, array $data = [], int $status = 200): ResponseInterface
    {
        return $this->responses->createResponse($status)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streams->createStream($this->templates->render($template, $data)));
    }

    /**
     * A 302 redirect: the browser follows it with a GET, so it's also right after a form post
     */
    public function redirect(string $location): ResponseInterface
    {
        return $this->responses->createResponse(302)->withHeader('Location', $location);
    }
}
