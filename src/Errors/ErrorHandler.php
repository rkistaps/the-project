<?php

declare(strict_types=1);

namespace TheProject\Errors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheApp\Interfaces\ConfigInterface;
use TheApp\Interfaces\ErrorHandlerInterface;
use TheProject\Routes\ApiRoutes;
use Throwable;

/**
 * The app's error handler. It answers by the request's path: JSON errors for the API, error pages for
 * the website. With APP_DEBUG on, website errors are rethrown, so they reach the Whoops debug page
 * (public/index.php); API errors stay JSON, with the exception's details.
 */
final class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private WebErrorHandler $web,
        private JsonErrorHandler $json,
        private ConfigInterface $config,
    ) {}

    public function handle(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        if (ApiRoutes::isApiPath($request->getUri()->getPath())) {
            return $this->json->handle($throwable, $request);
        }

        if ($this->config->get('debug')) {
            throw $throwable;
        }

        return $this->web->handle($throwable, $request);
    }
}
