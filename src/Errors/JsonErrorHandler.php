<?php

declare(strict_types=1);

namespace TheProject\Errors;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use TheApp\Exceptions\MethodNotAllowedException;
use TheApp\Exceptions\NoRouteMatchException;
use TheApp\Interfaces\ConfigInterface;
use TheApp\Interfaces\ErrorHandlerInterface;
use TheProject\Http\JsonResponder;
use Throwable;

/**
 * Turns uncaught exceptions in the API into JSON errors. The exception's details are included
 * only with APP_DEBUG on, so production responses never show paths or internal messages.
 */
final class JsonErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private JsonResponder $json,
        private ConfigInterface $config,
        private LoggerInterface $logger,
    ) {}

    public function handle(Throwable $throwable): ResponseInterface
    {
        // MethodNotAllowedException extends NoRouteMatchException, so it's checked first
        if ($throwable instanceof MethodNotAllowedException) {
            return $this->json->error(405, 'Method not allowed')
                ->withHeader('Allow', implode(', ', $throwable->getAllowedMethods()));
        }

        if ($throwable instanceof NoRouteMatchException) {
            return $this->json->error(404, 'Not found');
        }

        $this->logger->error('Uncaught exception in the API: ' . $throwable->getMessage(), ['exception' => $throwable]);

        $details = [];
        if ($this->config->get('debug')) {
            $details['debug'] = [
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile() . ':' . $throwable->getLine(),
            ];
        }

        return $this->json->error(500, 'Internal server error', $details);
    }
}
