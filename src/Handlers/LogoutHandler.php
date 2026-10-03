<?php

declare(strict_types=1);

namespace TheProject\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Auth\AuthService;
use TheProject\Http\HtmlResponder;
use TheProject\Routes\WebRoutes;
use TheProject\Session\Session;

/**
 * POST /logout: ends the session and goes to the login page. A POST with a CSRF token, so another
 * site can't sign the user out with a link or an image.
 */
final class LogoutHandler implements RequestHandlerInterface
{
    public function __construct(
        private AuthService $auth,
        private HtmlResponder $html,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->auth->signOut(Session::fromRequest($request));

        return $this->html->redirect(WebRoutes::LOGIN_PATH);
    }
}
