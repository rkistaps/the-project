<?php

declare(strict_types=1);

namespace TheProject\Core\Models;

use TheProject\Core\Abstracts\AbstractModel;

class User extends AbstractModel
{
    public string $username;
    public string $password;
}