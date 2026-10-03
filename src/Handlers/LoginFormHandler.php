<?php

declare(strict_types=1);

namespace TheProject\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Http\HtmlResponder;
use TheProject\Session\CsrfToken;
use TheProject\Session\Session;

/**
 * GET /login: the login form. AuthMiddleware sends signed-in users to the dashboard instead.
 */
final class LoginFormHandler implements RequestHandlerInterface
{
    public function __construct(
        private HtmlResponder $html,
        private CsrfToken $csrf,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->html->render('login', [
            'csrfToken' => $this->csrf->get(Session::fromRequest($request)),
            'username' => '',
            'error' => null,
        ]);
    }
}
