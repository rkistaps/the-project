<?php

declare(strict_types=1);

namespace TheProject\Tests\Core\Helpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TheProject\Core\Helpers\Env;

final class EnvTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/env-test-' . bin2hex(random_bytes(4));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        @unlink($this->directory . '/.env');
        rmdir($this->directory);
    }

    public function testLoadsVariablesFromDotEnv(): void
    {
        file_put_contents($this->directory . '/.env', "ENV_TEST_FROM_FILE=from-file\n");

        Env::load($this->directory);

        self::assertSame('from-file', Env::get('ENV_TEST_FROM_FILE'));
    }

    public function testRealEnvironmentWinsOverDotEnv(): void
    {
        putenv('ENV_TEST_REAL=from-environment');
        file_put_contents($this->directory . '/.env', "ENV_TEST_REAL=from-file\n");

        Env::load($this->directory);

        self::assertSame('from-environment', Env::get('ENV_TEST_REAL'));
        putenv('ENV_TEST_REAL');
    }

    public function testMissingDotEnvIsNotAnError(): void
    {
        Env::load($this->directory);

        self::assertSame('fallback', Env::get('ENV_TEST_MISSING', 'fallback'));
    }

    public static function boolProvider(): array
    {
        return [
            'true' => ['true', true],
            'one' => ['1', true],
            'on' => ['on', true],
            'false' => ['false', false],
            'zero' => ['0', false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('boolProvider')]
    public function testBool(string $value, bool $expected): void
    {
        putenv('ENV_TEST_BOOL=' . $value);

        self::assertSame($expected, Env::bool('ENV_TEST_BOOL', !$expected));
        putenv('ENV_TEST_BOOL');
    }

    public function testBoolDefaultWhenUnset(): void
    {
        self::assertTrue(Env::bool('ENV_TEST_BOOL_UNSET', true));
    }
}
