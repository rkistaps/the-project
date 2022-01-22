<?php

namespace TheProject\Handlers\Command\Test;

use TheApp\Interfaces\CommandHandlerInterface;

class TestCommandHandler implements CommandHandlerInterface
{
    public function handle(array $params = [])
    {
        echo 'Command handler with params: ' . print_r($params, true);
    }
}
