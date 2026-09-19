<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use TheApp\Apps\WebApp;
use TheApp\Exceptions\NoRouteMatchException;
use TheApp\Interfaces\RouterInterface;
use TheProject\Core\Factories\ContainerFactory;

/**
 * Runs requests through the real container and router, as public/index.php does.
 */
final class WebAppTest extends TestCase
{
    public static function routeProvider(): array
    {
        return [
            'handler' => ['/', 'This is content'],
            'callable' => ['/lorem', 'ipsum'],
            'route parameter' => ['/lorem/15/ipsum', 'Parameter given: 15'],
            'middleware' => ['/middleware', 'Demo middleware'],
            'anonymous middleware' => ['/anonymous-middleware', 'after'],
            'request attribute from middleware' => ['/authorized-user', 'Authorized user: Juris Testētājs'],
        ];
    }

    #[DataProvider('routeProvider')]
    public function testRouteResponds(string $path, string $expectedContent): void
    {
        $response = $this->request($path);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString($expectedContent, (string) $response->getBody());
    }

    public function testMiddlewaresWrapResponseInOrder(): void
    {
        $body = (string) $this->request('/multiple-middlewares')->getBody();

        self::assertStringStartsWith('Outer before<br>Inner before<br>', $body);
        self::assertStringEndsWith('Inner after<br>Outer after<br>', $body);
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

        return $container->get(WebApp::class)->run($request, $container->get(RouterInterface::class));
    }
}
