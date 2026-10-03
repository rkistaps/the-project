<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheProject\Auth\PasswordHasher;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Models\User;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Session\CsrfToken;

/**
 * Base for tests that send requests through the app, built the same way as public/index.php:
 * ApplicationFactory::web(), which serves both the website and the API.
 *
 * Like a browser, it keeps the cookies responses set and sends them with later requests, so a test
 * can sign in (signIn(), which needs UsesDatabase) and stay signed in.
 */
abstract class WebTestCase extends AppTestCase
{
    protected const PASSWORD = 'correct horse battery staple';

    /** @var array<string, string> */
    private array $cookies = [];

    /**
     * @param array<string, string> $headers
     */
    protected function get(string $path, array $headers = []): ResponseInterface
    {
        return $this->send($this->createRequest('GET', $path, $headers));
    }

    /**
     * Send a form, as a browser does
     *
     * @param array<string, mixed> $form
     */
    protected function post(string $path, array $form = []): ResponseInterface
    {
        $request = $this->createRequest('POST', $path, ['Content-Type' => 'application/x-www-form-urlencoded'])
            ->withParsedBody($form);

        return $this->send($request);
    }

    /**
     * @param array<string, string> $headers
     */
    protected function createRequest(string $method, string $path, array $headers = [], string $body = ''): ServerRequestInterface
    {
        $factory = new Psr17Factory();

        $request = $factory->createServerRequest($method, $path)->withBody($factory->createStream($body));
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($this->cookies !== []) {
            $request = $request->withCookieParams($this->cookies)
                ->withHeader('Cookie', http_build_query($this->cookies, '', '; '));
        }

        return $request;
    }

    protected function send(ServerRequestInterface $request): ResponseInterface
    {
        $response = ApplicationFactory::web($this->container())->run($request);

        foreach ($response->getHeader('Set-Cookie') as $header) {
            $this->rememberCookie($header);
        }

        return $response;
    }

    /**
     * @return array<string, string> The cookies the next request will send
     */
    protected function cookies(): array
    {
        return $this->cookies;
    }

    /**
     * The CSRF token from the form on a page, as a browser would post it back
     */
    protected function csrfToken(string $path = '/'): string
    {
        $html = (string) $this->get($path)->getBody();

        self::assertSame(1, preg_match('/name="' . CsrfToken::FIELD . '" value="([a-f0-9]{64})"/', $html, $matches), 'No CSRF token on ' . $path);

        return $matches[1];
    }

    protected function createUser(string $username = 'anna', ?string $password = self::PASSWORD, string $name = 'Anna', string $surname = 'Ozola'): User
    {
        return $this->container()->get(UserRepository::class)->createModel([
            'username' => $username,
            'password_hash' => $password === null ? null : $this->container()->get(PasswordHasher::class)->hash($password),
            'name' => $name,
            'surname' => $surname,
        ], true);
    }

    /**
     * Create a user and sign in through the login form
     */
    protected function signIn(string $username = 'anna'): User
    {
        $user = $this->createUser($username);

        $response = $this->post('/login', ['username' => $username, 'password' => self::PASSWORD, CsrfToken::FIELD => $this->csrfToken('/login')]);
        self::assertSame(302, $response->getStatusCode(), 'Signing in failed');

        return $user;
    }

    private function rememberCookie(string $header): void
    {
        $parts = explode(';', $header);
        [$name, $value] = explode('=', array_shift($parts), 2) + [1 => ''];

        $expired = false;
        foreach ($parts as $attribute) {
            if (strcasecmp(trim($attribute), 'Max-Age=0') === 0) {
                $expired = true;
            }
        }

        if ($expired) {
            unset($this->cookies[trim($name)]);
        } else {
            $this->cookies[trim($name)] = $value;
        }
    }
}
