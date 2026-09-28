<?php

namespace TheProject\Core\Collections;

use TheProject\Core\Abstracts\AbstractModelCollection;
use TheProject\Core\Models\User;

class UserCollection extends AbstractModelCollection
{
    public function getModelClassName(): string
    {
        return User::class;
    }
}