<?php

namespace TheProject\Core\Repositories;

use TheProject\Core\Abstracts\AbstractModelRepository;
use TheProject\Core\Collections\UserCollection;
use TheProject\Core\Models\User;

class UserRepository extends AbstractModelRepository
{
    protected function getTableName(): string
    {
        return 'users';
    }

    protected function getModelClassName(): string
    {
        return User::class;
    }

    protected function getCollectionClassName(): string
    {
        return UserCollection::class;
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findOne(['username' => $username]);
    }
}