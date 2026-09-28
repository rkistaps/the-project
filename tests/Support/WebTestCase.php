<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheProject\Core\Factories\ApplicationFactory;

/**
 * Base for tests that send requests through the app, built the same way as public/index.php:
 * ApplicationFactory::forRequest() picks the web app or the API by path.
 */
abstract class WebTestCase extends AppTestCase
{
    /**
     * @param array<string, string> $headers
     */
    protected function get(string $path, array $headers = []): ResponseInterface
    {
        return $this->send($this->createRequest('GET', $path, $headers));
    }

    /**
     * Send a form, as a browser does
     *
     * @param array<string, mixed> $form
     */
    protected function post(string $path, array $form = []): ResponseInterface
    {
        $request = $this->createRequest('POST', $path, ['Content-Type' => 'application/x-www-form-urlencoded'])
            ->withParsedBody($form);

        return $this->send($request);
    }

    /**
     * @param array<string, string> $headers
     */
    protected function createRequest(string $method, string $path, array $headers = [], string $body = ''): ServerRequestInterface
    {
        $factory = new Psr17Factory();

        $request = $factory->createServerRequest($method, $path)->withBody($factory->createStream($body));
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    protected function send(ServerRequestInterface $request): ResponseInterface
    {
        return ApplicationFactory::forRequest($this->container(), $request)->run($request);
    }
}
