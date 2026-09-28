<?php

declare(strict_types=1);

namespace TheProject\Core\Models;

use TheProject\Core\Abstracts\AbstractModel;

/**
 * Example model: a row of the users table (see migrations/), loaded and saved by UserRepository.
 */
class User extends AbstractModel
{
    public string $username;
    public string $email;
}
