<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use Opis\Database\Database;
use PHPUnit\Framework\Attributes\DataProvider;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Middlewares\SessionMiddleware;
use TheProject\Tests\Support\UsesDatabase;
use TheProject\Tests\Support\WebTestCase;

/**
 * Signing in and out of the website, against the test database. Each test's rows are rolled back.
 */
final class AuthTest extends WebTestCase
{
    use UsesDatabase;

    public static function protectedPaths(): array
    {
        return [
            'dashboard' => ['/'],
            'page with a parameter' => ['/hello/World'],
            // Paths no route takes too, so a visitor can't tell which pages exist
            'unknown path' => ['/does-not-exist'],
        ];
    }

    #[DataProvider('protectedPaths')]
    public function testVisitorWhoIsNotSignedInIsSentToLogin(string $path): void
    {
        $response = $this->get($path);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
    }

    public function testFormPostFromVisitorWhoIsNotSignedInIsSentToLogin(): void
    {
        $response = $this->post('/logout');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
    }

    public function testLoginPageShowsFormWithCsrfToken(): void
    {
        $response = $this->get('/login');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $html = (string) $response->getBody();
        self::assertStringContainsString('name="username"', $html);
        self::assertStringContainsString('name="password"', $html);
        self::assertMatchesRegularExpression('/name="_csrf" value="[a-f0-9]{64}"/', $html);
    }

    public function testSessionCookieIsHttpOnlyAndSameSiteLax(): void
    {
        $cookie = $this->get('/login')->getHeaderLine('Set-Cookie');

        self::assertMatchesRegularExpression('/^session=[a-f0-9]{64}; Path=\/; HttpOnly; SameSite=Lax$/', $cookie);
    }

    public function testSessionCookieIsSecureOverHttps(): void
    {
        $response = $this->send($this->createRequest('GET', 'https://localhost/login'));

        self::assertStringEndsWith('; Secure', $response->getHeaderLine('Set-Cookie'));
    }

    public function testCorrectCredentialsSignInAndRedirectToDashboard(): void
    {
        $this->createUser('anna');
        $token = $this->csrfToken('/login');
        $sessionBeforeSignIn = $this->cookies()[SessionMiddleware::COOKIE];

        $response = $this->post('/login', ['username' => 'anna', 'password' => self::PASSWORD, '_csrf' => $token]);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
        // A new session id on sign-in, so one known from before (session fixation) is worthless
        self::assertNotSame($sessionBeforeSignIn, $this->cookies()[SessionMiddleware::COOKIE]);
    }

    public function testOldSessionIdDoesNotSignInAfterSignIn(): void
    {
        $this->createUser('anna');
        $token = $this->csrfToken('/login');
        $sessionBeforeSignIn = $this->cookies()[SessionMiddleware::COOKIE];
        $this->post('/login', ['username' => 'anna', 'password' => self::PASSWORD, '_csrf' => $token]);

        $request = $this->createRequest('GET', '/')->withCookieParams([SessionMiddleware::COOKIE => $sessionBeforeSignIn]);

        self::assertSame(302, $this->send($request)->getStatusCode());
    }

    public function testDashboardShowsNameAndLogoutButton(): void
    {
        $this->signIn();

        $response = $this->get('/');

        self::assertSame(200, $response->getStatusCode());
        $html = (string) $response->getBody();
        self::assertStringContainsString('Anna Ozola', $html);
        self::assertStringContainsString('action="/logout"', $html);
        self::assertMatchesRegularExpression('/name="_csrf" value="[a-f0-9]{64}"/', $html);
    }

    public function testSignedInUserIsSentFromLoginToDashboard(): void
    {
        $this->signIn();

        $response = $this->get('/login');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
    }

    public static function wrongCredentials(): array
    {
        return [
            'wrong password' => ['anna', 'wrong password'],
            'unknown username' => ['nobody', self::PASSWORD],
            'empty fields' => ['', ''],
        ];
    }

    #[DataProvider('wrongCredentials')]
    public function testWrongCredentialsShowFormWithGenericError(string $username, string $password): void
    {
        $this->createUser('anna');

        $response = $this->post('/login', ['username' => $username, 'password' => $password, '_csrf' => $this->csrfToken('/login')]);

        self::assertSame(422, $response->getStatusCode());
        $html = (string) $response->getBody();
        self::assertStringContainsString('Invalid username or password', $html);
        self::assertStringContainsString('name="password"', $html);
        self::assertSame(302, $this->get('/')->getStatusCode(), 'Still not signed in');
    }

    public function testUserWithoutPasswordCannotSignIn(): void
    {
        $this->createUser('anna', null);

        $response = $this->post('/login', ['username' => 'anna', 'password' => '', '_csrf' => $this->csrfToken('/login')]);

        self::assertSame(422, $response->getStatusCode());
    }

    public function testLoginWithoutCsrfTokenIsRejected(): void
    {
        $this->createUser('anna');
        $this->get('/login');

        $response = $this->post('/login', ['username' => 'anna', 'password' => self::PASSWORD, '_csrf' => str_repeat('0', 64)]);

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('This form has expired', (string) $response->getBody());
        self::assertSame(302, $this->get('/')->getStatusCode(), 'Not signed in');
    }

    public function testLogoutWithoutCsrfTokenIsRejected(): void
    {
        $this->signIn();

        self::assertSame(403, $this->post('/logout')->getStatusCode());
        self::assertSame(200, $this->get('/')->getStatusCode(), 'Still signed in');
    }

    public function testLogoutEndsSession(): void
    {
        $this->signIn();
        $session = $this->cookies()[SessionMiddleware::COOKIE];

        $response = $this->post('/logout', ['_csrf' => $this->csrfToken('/')]);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
        self::assertStringContainsString('Max-Age=0', $response->getHeaderLine('Set-Cookie'));
        self::assertArrayNotHasKey(SessionMiddleware::COOKIE, $this->cookies());
        self::assertSame(302, $this->get('/')->getStatusCode());

        // The session is gone on the server too, so a copy of the cookie doesn't sign in
        $request = $this->createRequest('GET', '/')->withCookieParams([SessionMiddleware::COOKIE => $session]);
        self::assertSame(302, $this->send($request)->getStatusCode());
    }

    public function testDeletedUserIsSignedOut(): void
    {
        $user = $this->signIn();
        $this->container()->get(UserRepository::class)->deleteModel($user);

        self::assertSame(302, $this->get('/')->getStatusCode());
    }

    public function testExpiredSessionIsSignedOut(): void
    {
        $this->signIn();
        $this->container()->get(Database::class)->update('sessions')->set(['updated_at' => time() - 7201]);

        self::assertSame(302, $this->get('/')->getStatusCode());
    }

    public function testSessionIdIsStoredOnlyAsHash(): void
    {
        $this->signIn();
        $session = $this->cookies()[SessionMiddleware::COOKIE];

        $ids = $this->container()->get(Database::class)->from('sessions')->select(['id'])->fetchAssoc()->all();

        self::assertContains(['id' => hash('sha256', $session)], $ids);
        self::assertNotContains(['id' => $session], $ids);
    }

    public function testPasswordIsStoredAsHash(): void
    {
        $user = $this->createUser('anna');

        self::assertNotSame(self::PASSWORD, $user->passwordHash);
        self::assertTrue(password_verify(self::PASSWORD, (string) $user->passwordHash));
    }

    public function testApiHasNoSessionOrLogin(): void
    {
        $response = $this->get('/api/users', ['Accept' => 'application/json']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('Set-Cookie'));
    }
}
