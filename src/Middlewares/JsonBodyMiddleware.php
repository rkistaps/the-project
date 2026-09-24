<?php

declare(strict_types=1);

namespace TheProject\Middlewares;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Http\JsonResponder;

/**
 * Decodes a JSON request body into $request->getParsedBody(), so handlers read JSON the same way
 * as form data. Invalid JSON is answered with 400 and never reaches the handler.
 */
final class JsonBodyMiddleware implements MiddlewareInterface
{
    public function __construct(private JsonResponder $json)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = (string) $request->getBody();

        if ($this->isJson($request) && trim($body) !== '') {
            try {
                $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return $this->json->error(400, 'Invalid JSON: ' . $exception->getMessage());
            }

            if (!is_array($data)) {
                return $this->json->error(400, 'The JSON body must be an object');
            }

            $request = $request->withParsedBody($data);
        }

        return $handler->handle($request);
    }

    private function isJson(ServerRequestInterface $request): bool
    {
        // application/json, or a JSON-based type such as application/merge-patch+json
        $mediaType = strtolower(trim(explode(';', $request->getHeaderLine('Content-Type'))[0]));

        return $mediaType === 'application/json' || str_ends_with($mediaType, '+json');
    }
}
