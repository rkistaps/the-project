<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use DI\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;
use TheApp\Components\ArrayConfig;
use TheApp\Components\Router;
use TheApp\Exceptions\NoRouteMatchException;
use TheApp\Interfaces\ConfigInterface;
use TheApp\Interfaces\RouterConfiguratorInterface;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Factories\ContainerFactory;

/**
 * Runs requests through the web app as public/index.php builds it.
 */
final class WebAppTest extends TestCase
{
    /** @var list<string> Messages logged during the request */
    private array $logged = [];

    public function testHomePageRendersTemplate(): void
    {
        $response = $this->request('GET', '/');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('It works', (string) $response->getBody());
    }

    public function testMiddlewareAddsResponseTime(): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d{2}ms$/', $this->request('GET', '/')->getHeaderLine('X-Response-Time'));
    }

    public function testRouteParameterReachesHandler(): void
    {
        $response = $this->request('GET', '/hello/World');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello, World!', (string) $response->getBody());
    }

    public function testUnknownRouteIs404Page(): void
    {
        $response = $this->request('GET', '/does-not-exist');

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('Page not found', (string) $response->getBody());
        self::assertSame([], $this->logged);
    }

    public function testWrongMethodIs405PageWithAllowHeader(): void
    {
        $response = $this->request('POST', '/');

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, HEAD', $response->getHeaderLine('Allow'));
        self::assertStringContainsString('Method not allowed', (string) $response->getBody());
    }

    public function testExceptionIs500PageAndIsLogged(): void
    {
        $response = $this->request('GET', '/broken', extraRoutes: new class implements RouterConfiguratorInterface {
            public function configureRouter(Router $router): void
            {
                $router->get('/broken', function () {
                    throw new RuntimeException('Secret detail /var/www/html');
                });
            }
        });

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString('Something went wrong', (string) $response->getBody());
        self::assertStringNotContainsString('Secret detail', (string) $response->getBody());
        self::assertSame(['Uncaught exception in the web app: Secret detail /var/www/html'], $this->logged);
    }

    public function testWithDebugOnExceptionsAreLeftToWhoops(): void
    {
        $this->expectException(NoRouteMatchException::class);

        $this->request('GET', '/does-not-exist', debug: true);
    }

    private function request(
        string $method,
        string $path,
        bool $debug = false,
        ?RouterConfiguratorInterface $extraRoutes = null,
    ): ResponseInterface {
        $container = ContainerFactory::build();
        self::assertInstanceOf(Container::class, $container);

        // Pin APP_DEBUG, which otherwise comes from the environment, and capture what's logged
        $container->set(ConfigInterface::class, new ArrayConfig(['debug' => $debug] + require APP_ROOT . '/config/config.php'));
        $container->set(LoggerInterface::class, $this->logger());

        $app = ApplicationFactory::web($container);
        if ($extraRoutes !== null) {
            $app = $app->withRouterConfigurators([$extraRoutes]);
        }

        return $app->run((new Psr17Factory())->createServerRequest($method, $path));
    }

    private function logger(): LoggerInterface
    {
        $messages = &$this->logged;

        return new class ($messages) extends AbstractLogger {
            /** @param list<string> $messages */
            public function __construct(private array &$messages)
            {
            }

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->messages[] = (string) $message;
            }
        };
    }
}
