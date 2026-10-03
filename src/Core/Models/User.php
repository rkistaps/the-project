<?php

declare(strict_types=1);

namespace TheProject\Core\Models;

use TheProject\Core\Abstracts\AbstractModel;

/**
 * A row of the users table (see migrations/), loaded and saved by UserRepository.
 */
class User extends AbstractModel
{
    // 3 to 64 letters, digits, dots, dashes or underscores; the column holds 64
    public const USERNAME_PATTERN = '/^[A-Za-z0-9_.-]{3,64}$/';

    public string $username;
    // A password_hash() hash, never the password. Null means the user can't sign in.
    public ?string $passwordHash = null;
    public string $name;
    public string $surname;

    public function fullName(): string
    {
        return trim($this->name . ' ' . $this->surname);
    }
}
