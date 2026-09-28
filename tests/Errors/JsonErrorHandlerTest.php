<?php

declare(strict_types=1);

namespace TheProject\Tests\Errors;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use TheApp\Components\ArrayConfig;
use TheProject\Errors\JsonErrorHandler;
use TheProject\Http\JsonResponder;

final class JsonErrorHandlerTest extends TestCase
{
    public function testServerErrorHidesDetailsWithoutDebug(): void
    {
        $response = $this->handler(debug: false)->handle(new RuntimeException('secret path /var/www'));

        self::assertSame(500, $response->getStatusCode());
        self::assertSame(
            '{"error":{"status":500,"message":"Internal server error"}}',
            (string) $response->getBody()
        );
    }

    public function testServerErrorShowsDetailsWithDebug(): void
    {
        $response = $this->handler(debug: true)->handle(new RuntimeException('Something broke'));

        $debug = json_decode((string) $response->getBody(), true)['error']['debug'];
        self::assertSame(RuntimeException::class, $debug['exception']);
        self::assertSame('Something broke', $debug['message']);
        self::assertStringContainsString('JsonErrorHandlerTest.php:', $debug['file']);
    }

    private function handler(bool $debug): JsonErrorHandler
    {
        $factory = new Psr17Factory();

        return new JsonErrorHandler(new JsonResponder($factory, $factory), new ArrayConfig(['debug' => $debug]), new NullLogger());
    }
}
