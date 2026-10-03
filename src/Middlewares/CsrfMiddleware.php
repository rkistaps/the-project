<?php

declare(strict_types=1);

namespace TheProject\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Errors\CsrfTokenMismatchException;
use TheProject\Routes\ApiRoutes;
use TheProject\Session\CsrfToken;
use TheProject\Session\Session;

/**
 * Rejects website requests that change something (anything but GET, HEAD and OPTIONS) unless they carry
 * the session's CSRF token in the CsrfToken::FIELD form field. Runs after SessionMiddleware.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private CsrfToken $csrf) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), self::SAFE_METHODS, true) || ApiRoutes::isApiPath($request->getUri()->getPath())) {
            return $handler->handle($request);
        }

        $body = $request->getParsedBody();
        $token = is_array($body) ? ($body[CsrfToken::FIELD] ?? null) : null;

        if (!$this->csrf->isValid(Session::fromRequest($request), $token)) {
            throw new CsrfTokenMismatchException('The form was posted without a valid CSRF token');
        }

        return $handler->handle($request);
    }
}
