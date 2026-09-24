<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use TheApp\Exceptions\NoRouteMatchException;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Factories\ContainerFactory;

/**
 * Runs requests through the app as public/index.php builds it.
 */
final class WebAppTest extends TestCase
{
    public function testHomePageRendersTemplate(): void
    {
        $response = $this->request('/');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('It works', (string) $response->getBody());
    }

    public function testMiddlewareAddsResponseTime(): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d{2}ms$/', $this->request('/')->getHeaderLine('X-Response-Time'));
    }

    public function testRouteParameterReachesHandler(): void
    {
        $response = $this->request('/hello/World');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello, World!', (string) $response->getBody());
    }

    public function testUnknownRouteThrows(): void
    {
        $this->expectException(NoRouteMatchException::class);

        $this->request('/does-not-exist');
    }

    private function request(string $path): ResponseInterface
    {
        $container = ContainerFactory::build();
        $request = (new Psr17Factory())->createServerRequest('GET', $path);

        return ApplicationFactory::web($container)->run($request);
    }
}
