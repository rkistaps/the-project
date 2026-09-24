<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use Psr\Http\Message\ResponseInterface;

/**
 * Base for API tests: sends and reads JSON.
 */
abstract class ApiTestCase extends WebTestCase
{
    /**
     * Send a request with a JSON body (when $body is given)
     */
    protected function json(string $method, string $path, ?array $body = null): ResponseInterface
    {
        return $this->rawJson($method, $path, $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR));
    }

    /**
     * Send a body as JSON without encoding it, to test invalid JSON
     */
    protected function rawJson(string $method, string $path, string $body): ResponseInterface
    {
        return $this->send($this->createRequest($method, $path, ['Content-Type' => 'application/json', 'Accept' => 'application/json'], $body));
    }

    /**
     * The decoded JSON body, after checking the response is JSON
     */
    protected function decode(ResponseInterface $response): array
    {
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
