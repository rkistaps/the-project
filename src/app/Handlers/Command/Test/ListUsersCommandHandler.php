<?php

namespace TheProject\Handlers\Command\Test;

use TheProject\Core\Repositories\UserRepository;

class ListUsersCommandHandler implements \TheApp\Interfaces\CommandHandlerInterface
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(array $params = [])
    {
        $users = $this->repository->findAll();

        print_r($users);
    }
}