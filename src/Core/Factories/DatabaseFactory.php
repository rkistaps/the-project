<?php

namespace TheProject\Core\Factories;

use Opis\Database\Connection;
use Opis\Database\Database;
use TheProject\Core\Structures\DatabaseConfig;

class DatabaseFactory
{
    public function buildFromConfig(DatabaseConfig $config): Database
    {
        $dsn = 'mysql:host=' . $config->host . ';dbname=' . $config->name . ';charset=utf8mb4';
        if ($config->port !== '') {
            $dsn .= ';port=' . $config->port;
        }

        return new Database(new Connection($dsn, $config->username, $config->password));
    }
}
