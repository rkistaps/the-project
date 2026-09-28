<?php

// Configuration for phpmig (vendor/bin/phpmig). Migrations live in migrations/ and extend
// TheProject\Components\Migration, which gives them the app's container and database.

use Opis\Database\Database;
use Phpmig\Adapter\PDO\Sql;
use Psr\Container\ContainerInterface;
use TheProject\Core\Factories\ContainerFactory;

require_once __DIR__ . '/bootstrap.php';

$app = ContainerFactory::build();

return new ArrayObject([
    // Which migrations have run is kept in the database itself, in a `migrations` table that phpmig
    // creates on first use. So every database knows its own state, and a fresh clone starts with none run.
    'phpmig.adapter' => new Sql($app->get(Database::class)->getConnection()->getPDO(), 'migrations'),
    'phpmig.migrations_path' => __DIR__ . '/migrations',
    // What `phpmig generate` writes: a migration on this project's base class, with the database at hand
    'phpmig.migrations_template_path' => __DIR__ . '/migrations/.template',
    ContainerInterface::class => $app,
]);
