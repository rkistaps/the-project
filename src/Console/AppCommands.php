<?php

declare(strict_types=1);

namespace TheProject\Console;

use TheApp\Components\CommandRunner;
use TheApp\Interfaces\CommandConfiguratorInterface;
use TheApp\Interfaces\OutputInterface;

/**
 * Commands of the console app. Add a command here, or add another configurator to ApplicationFactory::console().
 */
final class AppCommands implements CommandConfiguratorInterface
{
    public function configureCommands(CommandRunner $commandRunner): void
    {
        // A callable command: options are matched to its parameters by name and type,
        // and class-typed parameters, like the output, come from the container.
        // ./run hello --name=World
        $commandRunner->addCommand('hello', function (OutputInterface $output, string $name = 'World') {
            $output->writeln("Hello, {$name}!");
        });

        // A command handler class, resolved from the container with its dependencies.
        // ./run create-user --username=juris --password=secret --name=Juris --surname=Bērziņš
        $commandRunner->addCommand('create-user', CreateUserCommand::class);
    }
}
