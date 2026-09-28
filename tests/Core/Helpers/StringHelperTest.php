<?php

declare(strict_types=1);

namespace TheProject\Tests\Core\Helpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TheProject\Core\Helpers\StringHelper;

final class StringHelperTest extends TestCase
{
    public static function caseProvider(): array
    {
        return [
            'from camel' => ['createdAt', 'created_at', 'createdAt', 'CreatedAt'],
            'from snake' => ['created_at', 'created_at', 'createdAt', 'CreatedAt'],
            'single word' => ['username', 'username', 'username', 'Username'],
        ];
    }

    #[DataProvider('caseProvider')]
    public function testConvertsCase(string $input, string $snake, string $camel, string $pascal): void
    {
        self::assertSame($snake, StringHelper::toSnakeCase($input));
        self::assertSame($camel, StringHelper::toCamelCase($input));
        self::assertSame($pascal, StringHelper::toPascal($input));
    }
}
