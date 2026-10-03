<?php

declare(strict_types=1);

namespace TheProject\Tests\Middlewares;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Opis\Database\Connection;
use Opis\Database\Database;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Auth\AuthService;
use TheProject\Auth\PasswordHasher;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Core\Services\ModelDataHydratorService;
use TheProject\Middlewares\AuthMiddleware;
use TheProject\Session\CsrfToken;
use TheProject\Session\Session;

/**
 * Where AuthMiddleware sends visitors who aren't signed in. A session without a user id never reaches the
 * database, so the connection here is never opened. Signed-in users are covered in Integration\AuthTest.
 */
final class AuthMiddlewareTest extends TestCase
{
    private ?ServerRequestInterface $handled = null;

    public function testVisitorIsRedirectedToLogin(): void
    {
        $response = $this->process(new ServerRequest('GET', '/'), new Session());

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
        self::assertNull($this->handled, 'The handler is never reached');
    }

    public function testVisitorPostingAFormIsRedirectedToLogin(): void
    {
        $response = $this->process(new ServerRequest('POST', '/logout'), new Session());

        self::assertSame(302, $response->getStatusCode());
        self::assertNull($this->handled);
    }

    public function testVisitorReachesLoginPage(): void
    {
        $response = $this->process(new ServerRequest('GET', '/login'), new Session());

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($this->handled);
    }

    public function testVisitorCanPostLoginForm(): void
    {
        $response = $this->process(new ServerRequest('POST', '/login'), new Session());

        self::assertSame(200, $response->getStatusCode());
    }

    public function testPathThatOnlyStartsWithLoginIsProtected(): void
    {
        self::assertSame(302, $this->process(new ServerRequest('GET', '/login-help'), new Session())->getStatusCode());
    }

    public function testApiIsLeftAlone(): void
    {
        // No session at all: SessionMiddleware doesn't run for the API either
        $response = $this->process(new ServerRequest('GET', '/api/users'), null);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($this->handled);
    }

    private function process(ServerRequestInterface $request, ?Session $session): ResponseInterface
    {
        if ($session !== null) {
            $request = $request->withAttribute(Session::class, $session);
        }

        $users = new UserRepository(new Database(new Connection('mysql:host=unused')), new ModelDataHydratorService());
        $middleware = new AuthMiddleware(new AuthService($users, new PasswordHasher(), new CsrfToken()), new Psr17Factory());

        $handler = new class implements RequestHandlerInterface {
            public ?ServerRequestInterface $request = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->request = $request;

                return new Response(200);
            }
        };

        $response = $middleware->process($request, $handler);
        $this->handled = $handler->request;

        return $response;
    }
}
