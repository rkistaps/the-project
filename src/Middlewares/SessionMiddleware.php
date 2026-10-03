<?php

declare(strict_types=1);

namespace TheProject\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Routes\ApiRoutes;
use TheProject\Session\Session;
use TheProject\Session\SessionStore;

/**
 * Loads the website session from its cookie before the handler, and saves it afterwards. The cookie
 * is only sent when its value changes: a new session, a new id on sign-in, or an ended session.
 *
 * The cookie is HttpOnly (scripts can't read it), SameSite=Lax (other sites can't send it with a form
 * post), and Secure when the request came over HTTPS. It has no expiry, so the browser drops it when
 * it closes; the server drops the session after SESSION_LIFETIME seconds without a request.
 */
final class SessionMiddleware implements MiddlewareInterface
{
    public const COOKIE = 'session';

    public function __construct(private SessionStore $store) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // The API has no sessions; how it authenticates is a separate concern
        if (ApiRoutes::isApiPath($request->getUri()->getPath())) {
            return $handler->handle($request);
        }

        $cookie = $request->getCookieParams()[self::COOKIE] ?? null;
        $cookie = is_string($cookie) ? $cookie : null;

        $session = $this->store->load($cookie);
        $response = $handler->handle($request->withAttribute(Session::class, $session));
        $this->store->save($session);

        if ($session->id() === $cookie) {
            return $response;
        }

        return $response->withAddedHeader('Set-Cookie', $this->cookie($session->id(), $request->getUri()->getScheme() === 'https'));
    }

    private function cookie(?string $id, bool $secure): string
    {
        $cookie = self::COOKIE . '=' . ($id ?? 'deleted') . '; Path=/; HttpOnly; SameSite=Lax';
        if ($id === null) {
            $cookie .= '; Max-Age=0; Expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }

        return $secure ? $cookie . '; Secure' : $cookie;
    }
}
