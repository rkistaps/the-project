<?php

declare(strict_types=1);

namespace TheProject\Core\Helpers;

use Jawira\CaseConverter\Convert;

class StringHelper
{
    public static function toSnakeCase(string $string): string
    {
        return (new Convert($string))->toSnake();
    }

    public static function toCamelCase(string $string): string
    {
        return (new Convert($string))->toCamel();
    }

    public static function toPascal(string $string): string
    {
        return (new Convert($string))->toPascal();
    }
}