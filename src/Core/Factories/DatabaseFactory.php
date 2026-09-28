<?php

namespace TheProject\Core\Factories;

use Opis\Database\Connection;
use Opis\Database\Database;
use TheProject\Core\Structures\DatabaseConfig;

class DatabaseFactory
{
    public function buildFromConfig(DatabaseConfig $config): Database
    {
        $connection = new Connection(
            'mysql:host=' . $config->host . ';dbname=' . $config->name,
            $config->username,
            $config->password
        );

        return new Database($connection);
    }
}