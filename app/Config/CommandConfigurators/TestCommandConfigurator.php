<?php

namespace TheProject\Config\CommandConfigurators;

use TheApp\Components\CommandRunner;
use TheApp\Interfaces\CommandConfiguratorInterface;
use TheProject\Handlers\Command\Test\TestCommandHandler;

class TestCommandConfigurator implements CommandConfiguratorInterface
{
    public function configureCommands(CommandRunner $commandRunner)
    {
        $commandRunner->addCommand('test', TestCommandHandler::class);
    }
}