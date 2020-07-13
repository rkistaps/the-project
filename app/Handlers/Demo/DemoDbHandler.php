<?php

namespace TheProject\Handlers\Demo;

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

        $result = $this->database->select('id')->from('mytable')->getColumns();
        dd($result);


    }
}