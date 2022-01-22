<?php

namespace TheProject\Core\Factories;

use Spiral\Database\Driver\MySQL\MySQLDriver;
use TheProject\Core\Structures\DatabaseConfig;
use Spiral\Database\Database;

class DatabaseFactory
{
    public function buildFromConfig(DatabaseConfig $databaseConfig): Database
    {
        $driver = new MySQLDriver([
            'connection' => 'mysql:host=' . $databaseConfig->host . ';dbname=' . $databaseConfig->name,
            'username' => $databaseConfig->username,
            'password' => $databaseConfig->password,
        ]);

        return new Database(
            $databaseConfig->name,
            '',
            $driver
        );
    }
}