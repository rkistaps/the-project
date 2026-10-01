<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Tests\Support\AppTestCase;
use TheProject\Tests\Support\UsesDatabase;

/**
 * Runs commands through the app as console.php builds it. What they write is read from
 * consoleOutput(): standard output and standard error apart.
 */
final class ConsoleAppTest extends AppTestCase
{
    use UsesDatabase;

    public function testCallableCommandGetsOptionsByName(): void
    {
        self::assertSame(0, $this->runConsole(['console.php', 'hello', '--name=Juris']));
        self::assertSame('Hello, Juris!' . PHP_EOL, $this->consoleOutput()->getOutput());
    }

    public function testCallableCommandUsesDefault(): void
    {
        self::assertSame(0, $this->runConsole(['console.php', '--command=hello']));
        self::assertSame('Hello, World!' . PHP_EOL, $this->consoleOutput()->getOutput());
    }

    public function testCommandHandlerCreatesUser(): void
    {
        self::assertSame(0, $this->runConsole(['console.php', 'create-user', '--username=juris', '--email=juris@example.com']));
        self::assertMatchesRegularExpression('/^Created user #\d+ juris' . PHP_EOL . '$/', $this->consoleOutput()->getOutput());
        self::assertSame('', $this->consoleOutput()->getErrors());
    }

    public function testCommandHandlerRejectsMissingOption(): void
    {
        self::assertSame(1, $this->runConsole(['console.php', 'create-user', '--username=juris']));
        self::assertSame('Missing required option --email' . PHP_EOL, $this->consoleOutput()->getErrors());
        self::assertSame('', $this->consoleOutput()->getOutput());
    }

    public function testUnknownCommandFails(): void
    {
        // On standard error, so it never ends up in redirected output such as ./run export > file.csv
        self::assertSame(1, $this->runConsole(['console.php', 'does-not-exist']));
        self::assertSame('Command not found' . PHP_EOL, $this->consoleOutput()->getErrors());
        self::assertSame('', $this->consoleOutput()->getOutput());
    }

    private function runConsole(array $argv): int
    {
        return ApplicationFactory::console($this->container())->run($argv);
    }
}
