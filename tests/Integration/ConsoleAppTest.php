<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use TheProject\Auth\PasswordHasher;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Repositories\UserRepository;
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

    public function testCommandHandlerCreatesUserWithHashedPassword(): void
    {
        self::assertSame(0, $this->runConsole(['console.php', 'create-user', '--username=juris', '--password=s3cret pass', '--name=Juris', '--surname=Bērziņš']));
        self::assertMatchesRegularExpression('/^Created user #\d+ juris' . PHP_EOL . '$/', $this->consoleOutput()->getOutput());
        self::assertSame('', $this->consoleOutput()->getErrors());

        $user = $this->container()->get(UserRepository::class)->findByUsername('juris');
        self::assertNotNull($user);
        self::assertSame('Juris', $user->name);
        self::assertSame('Bērziņš', $user->surname);
        self::assertNotSame('s3cret pass', $user->passwordHash);
        self::assertTrue($this->container()->get(PasswordHasher::class)->verify('s3cret pass', $user->passwordHash));
    }

    public static function missingOptions(): array
    {
        return [
            'username' => [['--password=secret', '--name=Juris', '--surname=Bērziņš'], 'username'],
            'password' => [['--username=juris', '--name=Juris', '--surname=Bērziņš'], 'password'],
            'name' => [['--username=juris', '--password=secret', '--surname=Bērziņš'], 'name'],
            'empty surname' => [['--username=juris', '--password=secret', '--name=Juris', '--surname= '], 'surname'],
        ];
    }

    /**
     * @param list<string> $options
     */
    #[DataProvider('missingOptions')]
    public function testCommandHandlerRejectsMissingOption(array $options, string $missing): void
    {
        self::assertSame(1, $this->runConsole(['console.php', 'create-user', ...$options]));
        self::assertSame('Missing required option --' . $missing . PHP_EOL, $this->consoleOutput()->getErrors());
        self::assertSame('', $this->consoleOutput()->getOutput());
    }

    public function testCommandHandlerRejectsDuplicateUsername(): void
    {
        $options = ['--username=juris', '--password=secret', '--name=Juris', '--surname=Bērziņš'];
        $this->runConsole(['console.php', 'create-user', ...$options]);

        self::assertSame(1, $this->runConsole(['console.php', 'create-user', ...$options]));
        self::assertSame('A user with the username "juris" already exists' . PHP_EOL, $this->consoleOutput()->getErrors());
        self::assertSame(1, $this->container()->get(UserRepository::class)->findAll(['username' => 'juris'])->count());
    }

    public function testCommandHandlerRejectsInvalidUsername(): void
    {
        self::assertSame(1, $this->runConsole(['console.php', 'create-user', '--username=a b', '--password=secret', '--name=Juris', '--surname=Bērziņš']));
        self::assertStringStartsWith('The username must be', $this->consoleOutput()->getErrors());
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
