<?php

namespace TheProject\Components;

use Opis\Database\Database;
use Psr\Container\ContainerInterface;

/**
 * Base class for migrations: gives them the app's container and database.
 */
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
