<?php

declare(strict_types=1);

namespace TheProject\Auth;

/**
 * Hashes and checks passwords with PHP's password_hash(), using its current default algorithm.
 */
final class PasswordHasher
{
    private static ?string $dummyHash = null;

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Whether the password matches the hash. With no hash (an unknown user, or one without a password)
     * the answer is false, but a password is still checked, so the time it takes doesn't tell an
     * unknown username from a wrong password.
     */
    public function verify(string $password, ?string $hash): bool
    {
        if ($hash === null) {
            password_verify($password, self::$dummyHash ??= $this->hash(bin2hex(random_bytes(16))));

            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * Whether the hash was made with an older algorithm or cost, and should be replaced on the next sign-in
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
