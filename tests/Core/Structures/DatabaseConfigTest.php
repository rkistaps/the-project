<?php

declare(strict_types=1);

namespace TheProject\Tests\Core\Structures;

use PHPUnit\Framework\TestCase;
use TheProject\Core\Structures\DatabaseConfig;

final class DatabaseConfigTest extends TestCase
{
    public function testFromArraySetsKnownPropertiesAndIgnoresOthers(): void
    {
        $config = DatabaseConfig::fromArray([
            'host' => 'db',
            'name' => 'theapp',
            'unknown' => 'ignored',
        ]);

        self::assertSame('db', $config->host);
        self::assertSame('theapp', $config->name);
        self::assertSame('', $config->port);
        self::assertFalse(property_exists($config, 'unknown'));
    }
}
