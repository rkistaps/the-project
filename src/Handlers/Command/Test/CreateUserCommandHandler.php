<?php

namespace TheProject\Handlers\Command\Test;

use TheApp\Interfaces\CommandHandlerInterface;
use TheProject\Core\Repositories\UserRepository;

class CreateUserCommandHandler implements CommandHandlerInterface
{
    private UserRepository $repository;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    public function handle(array $params = []): void
    {
        $model = $this->repository->createModel();
        $model->username = uniqid('User ');
        $model->password = rand();

        $this->repository->saveModel($model);
    }
}