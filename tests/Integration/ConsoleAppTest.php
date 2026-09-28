<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheApp\Factories\AppFactory;
use TheProject\Core\Factories\ContainerFactory;

/**
 * Runs commands through the real container, as console.php does.
 */
final class ConsoleAppTest extends TestCase
{
    public function testRunsCommandHandlerWithOptions(): void
    {
        $this->expectOutputRegex('/Command handler with params: .*\[lorem\] => ipsum/s');

        self::assertSame(0, $this->runConsole(['console.php', 'test', '--lorem=ipsum']));
    }

    public function testRunsCallableCommand(): void
    {
        $this->expectOutputRegex('/This is callable command with foo = foo1/');

        self::assertSame(0, $this->runConsole(['console.php', '--command=callable', '--foo=foo1']));
    }

    public function testUnknownCommandFails(): void
    {
        $this->expectOutputString('Command not found' . PHP_EOL);

        self::assertSame(1, $this->runConsole(['console.php', 'does-not-exist']));
    }

    private function runConsole(array $argv): int
    {
        return AppFactory::consoleAppFromContainer(ContainerFactory::build())->run($argv);
    }
}
