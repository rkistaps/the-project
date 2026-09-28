<?php

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use TheApp\Components\CommandRunner;
use TheApp\Factories\CommandHandlerFactory;
use TheProject\Console\CreateUserCommand;

class CommandRunnerFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function build(): CommandRunner
    {
        $runner = new CommandRunner($this->container->get(CommandHandlerFactory::class));

        // A callable command: options are matched to its parameters by name and type.
        // ./run hello --name=World
        $runner->addCommand('hello', function (string $name = 'World') {
            echo "Hello, {$name}!" . PHP_EOL;
        });

        // A command handler class, resolved from the container with its dependencies.
        // ./run create-user --username=juris --email=juris@example.com
        $runner->addCommand('create-user', CreateUserCommand::class);

        return $runner;
    }
}
