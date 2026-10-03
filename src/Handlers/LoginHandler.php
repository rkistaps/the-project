<?php

declare(strict_types=1);

namespace TheProject\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Auth\AuthService;
use TheProject\Http\HtmlResponder;
use TheProject\Routes\WebRoutes;
use TheProject\Session\CsrfToken;
use TheProject\Session\Session;

/**
 * POST /login with the username and password. Signs in and redirects to the dashboard, or shows the form
 * again with one message for any wrong username or password. CsrfMiddleware has checked the token.
 */
final class LoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private AuthService $auth,
        private HtmlResponder $html,
        private CsrfToken $csrf,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $username = is_array($body) && is_string($body['username'] ?? null) ? trim($body['username']) : '';
        $password = is_array($body) && is_string($body['password'] ?? null) ? $body['password'] : '';

        $session = Session::fromRequest($request);

        if ($this->auth->signIn($session, $username, $password) !== null) {
            return $this->html->redirect(WebRoutes::HOME_PATH);
        }

        return $this->html->render('login', [
            'csrfToken' => $this->csrf->get($session),
            'username' => $username,
            'error' => 'Invalid username or password',
        ], 422);
    }
}
