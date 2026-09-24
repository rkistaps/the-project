<?php

declare(strict_types=1);

namespace TheProject\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Builds JSON responses for the API. Errors all have the same shape:
 * {"error": {"status": 404, "message": "Not found", ...details}}
 */
final class JsonResponder
{
    public function __construct(
        private ResponseFactoryInterface $responses,
        private StreamFactoryInterface $streams,
    ) {}

    public function respond(mixed $data, int $status = 200): ResponseInterface
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->responses->createResponse($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streams->createStream($json));
    }

    /**
     * @param array<string, mixed> $details Extra keys for the error object, such as the invalid fields
     */
    public function error(int $status, string $message, array $details = []): ResponseInterface
    {
        return $this->respond(['error' => ['status' => $status, 'message' => $message] + $details], $status);
    }
}
