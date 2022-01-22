<?php

namespace TheProject\Handlers\Request\Demo;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Database\Database;

class DemoDbHandler implements RequestHandlerInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
//        $schema = $this->database->table('test')->getSchema();
//        $schema->primary('id');
//        $schema->string('name');
//        $schema->string('surname');
//        $schema->save();
//
//        $this->database->insert('test')->values([
//            'name' => 'Juris',
//            'surname' => 'Testētājs',
//        ])->run();

        $result = $this->database->table('test')->select()->where(['id' => 1])->fetchAll();

        dd($result);
    }
}