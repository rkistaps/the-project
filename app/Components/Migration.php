<?php

namespace TheProject\Components;

use Psr\Container\ContainerInterface;
use Spiral\Database\Database;

class Migration extends \Phpmig\Migration\Migration
{
    public function getContainer(): ContainerInterface
    {
        return parent::getContainer()[ContainerInterface::class];
    }

    public function getDatabase(): Database
    {
        return $this->getContainer()->get(Database::class);
    }
}