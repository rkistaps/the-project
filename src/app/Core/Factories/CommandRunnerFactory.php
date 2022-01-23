<?php

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use TheApp\Components\CommandRunner;
use TheApp\Factories\CommandHandlerFactory;
use TheProject\Handlers\Command\Test\CreateUserCommandHandler;
use TheProject\Handlers\Command\Test\ListUsersCommandHandler;
use TheProject\Handlers\Command\Test\TestCommandHandler;
use TheProject\Handlers\Request\Demo\DemoDbHandler;

class CommandRunnerFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function build(): CommandRunner
    {
        $runner = new CommandRunner(
            $this->container,
            $this->container->get(CommandHandlerFactory::class)
        );

        // regular command handler
        // ./run test -lorem=ipsum
        $runner->addCommand('test', TestCommandHandler::class);

        // callable command handler
        // ./run callable -foo=foo1 -bar=bar1
        $runner->addCommand('callable',
            function ($foo, $bar = null) {
                echo 'This is callable command with foo = ' . print_r($foo, true) . PHP_EOL;
                echo 'Bar is ' . PHP_EOL;
                var_dump($bar);
                echo PHP_EOL;
            }
        );

        // ./run create-user
        $runner->addCommand('create-user', CreateUserCommandHandler::class);

        // ./run list-users
        $runner->addCommand('list-users', ListUsersCommandHandler::class);

        return $runner;
    }
}