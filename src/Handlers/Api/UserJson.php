<?php

declare(strict_types=1);

namespace TheProject\Handlers\Api;

use TheProject\Core\Models\User;

/**
 * How a user looks in the API. The fields are listed one by one, so a column added to the
 * model isn't exposed until it's added here too.
 */
final class UserJson
{
    /**
     * @return array{id: int, username: string, email: string}
     */
    public static function from(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
}
