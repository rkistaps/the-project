<?php

namespace TheProject\Config\CommandConfigurators;

use TheApp\Components\CommandRunner;
use TheApp\Interfaces\CommandConfiguratorInterface;
use TheProject\Handlers\Command\Test\TestCommandHandler;

class TestCommandConfigurator implements CommandConfiguratorInterface
{
    public function configureCommands(CommandRunner $commandRunner)
    {
        // regular command handler
        $commandRunner->addCommand('test', TestCommandHandler::class);

        // callable command handler
        $commandRunner->addCommand('callable',
            function ($foo, $bar = null) {
                echo 'This is callable command with foo = ' . print_r($foo, true) . PHP_EOL;
                echo 'Bar is ';
                var_dump($bar);
                echo PHP_EOL;
            }
        );
    }
}