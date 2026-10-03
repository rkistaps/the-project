<?php

declare(strict_types=1);

namespace TheProject\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Auth\AuthService;
use TheProject\Core\Models\User;
use TheProject\Routes\ApiRoutes;
use TheProject\Routes\WebRoutes;
use TheProject\Session\Session;

/**
 * Keeps the website to signed-in users. Visitors who aren't signed in are redirected to the login page
 * from every path, including ones no route takes, so the site doesn't reveal which pages exist.
 * Signed-in users get the User::class request attribute, and are sent from the login page to the dashboard.
 *
 * Static files never get here: Apache serves anything that exists in public/ (see public/.htaccess).
 * The API is left alone; its authentication is a separate concern.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $auth,
        private ResponseFactoryInterface $responses,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (ApiRoutes::isApiPath($path)) {
            return $handler->handle($request);
        }

        $user = $this->auth->currentUser(Session::fromRequest($request));
        $isLoginPage = $path === WebRoutes::LOGIN_PATH;

        if ($user === null) {
            return $isLoginPage ? $handler->handle($request) : $this->redirect(WebRoutes::LOGIN_PATH);
        }

        if ($isLoginPage) {
            return $this->redirect(WebRoutes::HOME_PATH);
        }

        return $handler->handle($request->withAttribute(User::class, $user));
    }

    private function redirect(string $location): ResponseInterface
    {
        return $this->responses->createResponse(302)->withHeader('Location', $location);
    }
}
