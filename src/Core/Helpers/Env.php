<?php

declare(strict_types=1);

namespace TheProject\Core\Helpers;

use Dotenv\Dotenv;

/**
 * Reads settings from environment variables, with a .env file as the fallback for local development.
 */
final class Env
{
    /**
     * Load the .env file in the directory, if there is one. Variables that are already set in the
     * real environment (Docker, CI, the shell) keep their values.
     */
    public static function load(string $directory): void
    {
        // "Unsafe" means it also reads and writes getenv()/putenv(), which is how it sees the real environment.
        Dotenv::createUnsafeImmutable($directory)->safeLoad();
    }

    public static function get(string $name, ?string $default = null): ?string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        return is_string($value) ? $value : $default;
    }

    /**
     * "true", "1", "yes" and "on" are true; any other set value is false.
     */
    public static function bool(string $name, bool $default = false): bool
    {
        $value = self::get($name);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
