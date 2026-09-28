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
    public function testCallableCommandGetsOptionsByName(): void
    {
        $this->expectOutputString('Hello, Juris!' . PHP_EOL);

        self::assertSame(0, $this->runConsole(['console.php', 'hello', '--name=Juris']));
    }

    public function testCallableCommandUsesDefault(): void
    {
        $this->expectOutputString('Hello, World!' . PHP_EOL);

        self::assertSame(0, $this->runConsole(['console.php', '--command=hello']));
    }

    public function testCommandHandlerRejectsMissingOption(): void
    {
        $this->expectOutputString('Missing required option --email' . PHP_EOL);

        self::assertSame(1, $this->runConsole(['console.php', 'create-user', '--username=juris']));
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
