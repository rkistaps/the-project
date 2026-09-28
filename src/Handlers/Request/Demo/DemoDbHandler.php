<?php

namespace TheProject\Handlers\Request\Demo;

use Opis\Database\Database;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheApp\Components\Builders\ResponseBuilder;

class DemoDbHandler implements RequestHandlerInterface
{
    private Database $database;
    private ResponseBuilder $responseBuilder;

    public function __construct(Database $database, ResponseBuilder $responseBuilder)
    {
        $this->database = $database;
        $this->responseBuilder = $responseBuilder;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tables = $this->database->schema()->getTables();

        $response = $this->responseBuilder->build();

        $response->getBody()->write('DB tables: <br />');
        foreach ($tables as $table) {
            $response->getBody()->write($table . '<br />');
        }

        return $response;
    }
}