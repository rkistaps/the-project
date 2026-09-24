<?php

declare(strict_types=1);

namespace TheProject\Console;

use TheApp\Components\CommandRunner;
use TheApp\Interfaces\CommandConfiguratorInterface;

/**
 * Commands of the console app. Add a command here, or add another configurator to ApplicationFactory::console().
 */
final class AppCommands implements CommandConfiguratorInterface
{
    public function configureCommands(CommandRunner $commandRunner): void
    {
        // A callable command: options are matched to its parameters by name and type.
        // ./run hello --name=World
        $commandRunner->addCommand('hello', function (string $name = 'World') {
            echo "Hello, {$name}!" . PHP_EOL;
        });

        // A command handler class, resolved from the container with its dependencies.
        // ./run create-user --username=juris --email=juris@example.com
        $commandRunner->addCommand('create-user', CreateUserCommand::class);
    }
}
