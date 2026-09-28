<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Tests\Support\AppTestCase;

/**
 * Runs commands through the app as console.php builds it.
 */
final class ConsoleAppTest extends AppTestCase
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
        return ApplicationFactory::console($this->container())->run($argv);
    }
}
