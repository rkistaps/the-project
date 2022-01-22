<?php

namespace TheProject\Core\Factories;

use Spiral\Database\Driver\MySQL\MySQLDriver;
use TheProject\Core\Structures\DatabaseConfig;

class DatabaseFactory
{
    public function buildFromConfig(DatabaseConfig $databaseConfig): Spiral\Database\Database
    {
        $driver = new MySQLDriver([
            'connection' => 'mysql:host=' . $databaseConfig->host . ';dbname=' . $databaseConfig->name,
            'username' => $databaseConfig->username,
            'password' => $databaseConfig->password,
        ]);

        return (new Spiral\Database\Database(
            $databaseConfig->name,
            '',
            $driver
        ));
    }
}