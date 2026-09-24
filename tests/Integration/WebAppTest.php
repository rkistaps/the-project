<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use RuntimeException;
use TheApp\Components\Router;
use TheApp\Exceptions\NoRouteMatchException;
use TheApp\Interfaces\RouterConfiguratorInterface;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Tests\Support\WebTestCase;

final class WebAppTest extends WebTestCase
{
    public function testHomePageRendersTemplate(): void
    {
        $response = $this->get('/');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('It works', (string) $response->getBody());
    }

    public function testMiddlewareAddsResponseTime(): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d{2}ms$/', $this->get('/')->getHeaderLine('X-Response-Time'));
    }

    public function testRouteParameterReachesHandler(): void
    {
        $response = $this->get('/hello/World');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello, World!', (string) $response->getBody());
    }

    public function testUnknownRouteIs404Page(): void
    {
        $response = $this->get('/does-not-exist');

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('Page not found', (string) $response->getBody());
        self::assertSame([], $this->loggedMessages());
    }

    public function testWrongMethodIs405PageWithAllowHeader(): void
    {
        $response = $this->post('/');

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, HEAD', $response->getHeaderLine('Allow'));
        self::assertStringContainsString('Method not allowed', (string) $response->getBody());
    }

    public function testExceptionIs500PageAndIsLogged(): void
    {
        // A route that throws, added to the real web app for this test
        $app = ApplicationFactory::web($this->container())->withRouterConfigurators([
            new class implements RouterConfiguratorInterface {
                public function configureRouter(Router $router): void
                {
                    $router->get('/broken', function () {
                        throw new RuntimeException('Secret detail /var/www/html');
                    });
                }
            },
        ]);

        $response = $app->run($this->createRequest('GET', '/broken'));

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString('Something went wrong', (string) $response->getBody());
        self::assertStringNotContainsString('Secret detail', (string) $response->getBody());
        self::assertSame(['Uncaught exception in the web app: Secret detail /var/www/html'], $this->loggedMessages());
    }

    public function testWithDebugOnExceptionsAreLeftToWhoops(): void
    {
        $this->withDebug();

        $this->expectException(NoRouteMatchException::class);

        $this->get('/does-not-exist');
    }
}
